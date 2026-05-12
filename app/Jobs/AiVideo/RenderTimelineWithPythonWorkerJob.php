<?php

namespace App\Jobs\AiVideo;

use App\Enums\RenderJobStatus;
use App\Models\RenderJob;
use App\Models\VideoGeneration;
use App\Models\VideoVersion;
use App\Services\AiVideo\PythonAiWorkerClient;
use App\Services\AiVideo\TimelineManifestBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Throwable;

class RenderTimelineWithPythonWorkerJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public int $tries = 3;

    public function __construct(private readonly int $renderJobId)
    {
        $this->onQueue((string) config('ai_video_platform.queues.gpu', 'ai-gpu'));
    }

    public function handle(TimelineManifestBuilder $manifestBuilder, PythonAiWorkerClient $worker): void
    {
        $renderJob = RenderJob::query()->with('videoProject')->find($this->renderJobId);

        if (!$renderJob || !$renderJob->videoProject || $renderJob->status?->isTerminal()) {
            return;
        }

        $renderJob->update([
            'status' => RenderJobStatus::Rendering,
            'progress' => 20,
            'current_step' => 'Sending timeline manifest to Python AI worker',
            'started_at' => $renderJob->started_at ?: now(),
        ]);

        try {
            $response = $worker->renderTimeline($manifestBuilder->build($renderJob->videoProject)->toArray());

            $renderJob->update([
                'status' => RenderJobStatus::Completed,
                'progress' => 100,
                'current_step' => 'Python AI worker render completed',
                'output_payload' => $response,
                'finished_at' => now(),
            ]);

            $this->markVersionCompleted($renderJob, $response['output_path'] ?? null);
        } catch (Throwable $exception) {
            $renderJob->update([
                'status' => RenderJobStatus::Failed,
                'current_step' => 'Python AI worker render failed',
                'error_message' => Str::limit($exception->getMessage(), 2000),
                'finished_at' => now(),
            ]);

            $this->markVersionFailed($renderJob, $exception);

            throw $exception;
        }
    }

    private function markVersionCompleted(RenderJob $renderJob, ?string $outputPath): void
    {
        $version = $this->version($renderJob);
        if (!$version) {
            return;
        }

        $version->update([
            'status' => 'completed',
            'progress' => 100,
            'output_url' => $outputPath,
        ]);

        if ($version->generation) {
            $this->refreshGenerationStatus($version->generation);
        }
    }

    private function markVersionFailed(RenderJob $renderJob, Throwable $exception): void
    {
        $version = $this->version($renderJob);
        if (!$version) {
            return;
        }

        $version->update([
            'status' => 'failed',
            'progress' => 100,
            'error_message' => Str::limit($exception->getMessage(), 2000),
        ]);

        if ($version->generation) {
            $this->refreshGenerationStatus($version->generation);
        }
    }

    private function version(RenderJob $renderJob): ?VideoVersion
    {
        $versionId = data_get($renderJob->input_payload, 'video_version_id');

        return $versionId
            ? VideoVersion::query()->with('generation.versions')->find($versionId)
            : VideoVersion::query()->with('generation.versions')->where('render_job_id', $renderJob->id)->first();
    }

    private function refreshGenerationStatus(VideoGeneration $generation): void
    {
        $generation->load('versions');
        $completed = $generation->versions->where('status', 'completed')->count();
        $failed = $generation->versions->where('status', 'failed')->count();
        $running = $generation->versions
            ->whereIn('status', ['queued', 'processing', 'assets_ready', 'rendering'])
            ->count();

        $generation->update([
            'completed_versions' => $completed,
            'failed_versions' => $failed,
            'status' => $running > 0 ? 'processing' : ($failed > 0 ? 'partial' : 'completed'),
        ]);
    }
}
