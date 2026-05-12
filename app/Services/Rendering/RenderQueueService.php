<?php

namespace App\Services\Rendering;

use App\Enums\RenderJobStatus;
use App\Enums\RenderJobType;
use App\Jobs\AiVideo\RenderTimelineWithPythonWorkerJob;
use App\Jobs\AiVideo\RenderVideoVersionWithRemotionJob;
use App\Jobs\AiVideo\RenderVideoVersionWithShotstackJob;
use App\Models\Export;
use App\Models\RenderJob;
use App\Models\VideoVersion;
use Illuminate\Support\Str;
use RuntimeException;

class RenderQueueService
{
    public function __construct(
        private readonly RenderJobDispatcher $ffmpeg,
        private readonly TimelineBuilder $timelineBuilder,
    ) {
    }

    public function queueVersion(VideoVersion $version, ?string $provider = null): RenderJob
    {
        $version->loadMissing('videoProject');
        $provider = $provider ?: (string) data_get($version->generation?->settings, 'render_provider', config('bulk_ai_video.render.default_provider', 'ffmpeg'));

        if (!$version->videoProject) {
            throw new RuntimeException('Cannot render a video version without a video project.');
        }

        return match ($provider) {
            'remotion' => $this->queueRemotion($version),
            'shotstack' => $this->queueShotstack($version),
            'python' => $this->queuePython($version),
            default => $this->queueFfmpeg($version),
        };
    }

    private function queueRemotion(VideoVersion $version): RenderJob
    {
        $payload = array_replace(
            $this->timelineBuilder->buildForVersion($version)->toArray(),
            [
                'video_version_id' => $version->id,
                'renderer' => 'remotion',
                'final_encoder' => 'ffmpeg',
            ]
        );

        $renderJob = $this->createRenderJob($version, 'remotion', $payload);

        $version->update([
            'render_job_id' => $renderJob->id,
            'status' => 'rendering',
            'progress' => 80,
            'timeline_json' => $payload,
        ]);

        if (config('ai_video.queue.mode', 'queue') === 'sync') {
            dispatch_sync(new RenderVideoVersionWithRemotionJob($renderJob->id));

            return $renderJob->refresh();
        }

        RenderVideoVersionWithRemotionJob::dispatch($renderJob->id)
            ->onQueue((string) config('bulk_ai_video.render.queue', 'render'));

        return $renderJob;
    }

    private function queueFfmpeg(VideoVersion $version): RenderJob
    {
        $renderJob = $this->ffmpeg->dispatch($version->videoProject, [
            'aspect_ratio' => $version->aspect_ratio,
            'source' => 'bulk_ai_video',
            'video_version_id' => $version->id,
        ]);

        $jobStatus = $renderJob->status?->value ?? (string) $renderJob->status;
        $exportId = (int) data_get($renderJob->output_payload, 'export_id');
        $export = $exportId > 0 ? Export::query()->find($exportId) : null;
        $outputUrl = $export?->file_path ? asset('storage/' . ltrim((string) $export->file_path, '/')) : null;

        $version->update([
            'render_job_id' => $renderJob->id,
            'status' => match (true) {
                $jobStatus === RenderJobStatus::Completed->value => 'completed',
                $jobStatus === RenderJobStatus::Failed->value => 'failed',
                $jobStatus === RenderJobStatus::Cancelled->value => 'cancelled',
                default => 'rendering',
            },
            'progress' => match (true) {
                in_array($jobStatus, [RenderJobStatus::Completed->value, RenderJobStatus::Failed->value, RenderJobStatus::Cancelled->value], true) => 100,
                default => max(80, (int) $renderJob->progress),
            },
            'error_message' => $renderJob->error_message,
            'output_url' => $outputUrl ?: $version->output_url,
        ]);

        return $renderJob;
    }

    private function queuePython(VideoVersion $version): RenderJob
    {
        $renderJob = $this->createRenderJob($version, 'python-worker', array_replace(
            $this->timelineBuilder->buildForVersion($version)->toArray(),
            ['video_version_id' => $version->id]
        ));

        RenderTimelineWithPythonWorkerJob::dispatch($renderJob->id)
            ->onQueue((string) config('ai_video_platform.queues.gpu', 'ai-gpu'));

        $version->update([
            'render_job_id' => $renderJob->id,
            'status' => 'rendering',
            'progress' => 80,
        ]);

        return $renderJob;
    }

    private function queueShotstack(VideoVersion $version): RenderJob
    {
        $payload = $this->timelineBuilder->buildShotstackPayload($version);
        $renderJob = $this->createRenderJob($version, 'shotstack', $payload);

        RenderVideoVersionWithShotstackJob::dispatch($renderJob->id)
            ->onQueue((string) config('bulk_ai_video.render.queue', 'render'));

        $version->update([
            'render_job_id' => $renderJob->id,
            'status' => 'rendering',
            'progress' => 80,
            'timeline_json' => $payload,
        ]);

        return $renderJob;
    }

    private function createRenderJob(VideoVersion $version, string $provider, array $payload): RenderJob
    {
        return RenderJob::create([
            'uuid' => (string) Str::uuid(),
            'video_project_id' => $version->video_project_id,
            'type' => RenderJobType::RenderFinalVideo,
            'status' => RenderJobStatus::Queued,
            'queue' => (string) config('bulk_ai_video.render.queue', 'render'),
            'provider' => $provider,
            'progress' => 0,
            'max_attempts' => 3,
            'current_step' => 'Queued bulk AI video render',
            'input_payload' => $payload,
            'available_at' => now(),
        ]);
    }
}
