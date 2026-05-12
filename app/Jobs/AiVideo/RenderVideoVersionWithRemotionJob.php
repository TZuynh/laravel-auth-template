<?php

namespace App\Jobs\AiVideo;

use App\Enums\RenderJobStatus;
use App\Models\RenderJob;
use App\Models\VideoGeneration;
use App\Models\VideoVersion;
use App\Services\Rendering\RemotionRenderPipeline;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Throwable;

class RenderVideoVersionWithRemotionJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public int $tries = 2;

    public function __construct(private readonly int $renderJobId)
    {
        $this->onQueue((string) config('bulk_ai_video.render.queue', 'render'));
    }

    public function handle(RemotionRenderPipeline $pipeline): void
    {
        $renderJob = RenderJob::query()->with('videoProject')->find($this->renderJobId);
        if (!$renderJob || !$renderJob->videoProject || $renderJob->status?->isTerminal()) {
            return;
        }

        $version = $this->version($renderJob);
        if (!$version || in_array($version->status, ['cancelled', 'completed'], true)) {
            return;
        }

        $renderJob->update([
            'attempts' => $renderJob->attempts + 1,
            'status' => RenderJobStatus::Preparing,
            'progress' => max(80, (int) $renderJob->progress),
            'current_step' => 'Preparing Remotion cinematic renderer',
            'started_at' => $renderJob->started_at ?: now(),
            'error_message' => null,
        ]);

        $version->update([
            'status' => 'rendering',
            'progress' => max(82, (int) $version->progress),
            'error_message' => null,
        ]);

        try {
            $export = $pipeline->render($version, $renderJob);

            $version->update([
                'status' => 'completed',
                'progress' => 100,
                'output_url' => $export->file_path ? asset('storage/' . ltrim((string) $export->file_path, '/')) : null,
                'error_message' => null,
            ]);

            $this->refreshGenerationStatus($version->generation);
        } catch (Throwable $exception) {
            $this->markFailed($renderJob, $exception);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        $renderJob = RenderJob::query()->find($this->renderJobId);
        if ($renderJob) {
            $this->markFailed($renderJob, $exception);
        }
    }

    private function markFailed(RenderJob $renderJob, Throwable $exception): void
    {
        $message = Str::limit($exception->getMessage(), 2000);

        $renderJob->update([
            'status' => RenderJobStatus::Failed,
            'progress' => 100,
            'current_step' => 'Remotion render failed',
            'error_message' => $message,
            'finished_at' => now(),
        ]);

        $version = $this->version($renderJob);
        $version?->update([
            'status' => 'failed',
            'progress' => 100,
            'error_message' => $message,
        ]);

        if ($version?->generation) {
            $this->refreshGenerationStatus($version->generation);
        }
    }

    private function version(RenderJob $renderJob): ?VideoVersion
    {
        $versionId = (int) data_get($renderJob->input_payload, 'video_version_id');

        return $versionId > 0
            ? VideoVersion::query()->with(['generation.versions', 'videoProject.scenes.sceneAssets', 'voiceTracks', 'subtitleTracks'])->find($versionId)
            : VideoVersion::query()->with(['generation.versions', 'videoProject.scenes.sceneAssets', 'voiceTracks', 'subtitleTracks'])->where('render_job_id', $renderJob->id)->first();
    }

    private function refreshGenerationStatus(?VideoGeneration $generation): void
    {
        if (!$generation) {
            return;
        }

        $generation->load('versions');
        $completed = $generation->versions->where('status', 'completed')->count();
        $failed = $generation->versions->where('status', 'failed')->count();
        $cancelled = $generation->versions->where('status', 'cancelled')->count();
        $running = $generation->versions
            ->whereIn('status', ['queued', 'processing', 'assets_ready', 'rendering'])
            ->count();

        $generation->update([
            'completed_versions' => $completed,
            'failed_versions' => $failed,
            'status' => match (true) {
                $running > 0 => 'processing',
                $completed > 0 && $failed === 0 && $cancelled === 0 => 'completed',
                $cancelled > 0 && $completed === 0 && $failed === 0 => 'cancelled',
                $failed > 0 => 'partial',
                default => 'completed',
            },
        ]);
    }
}
