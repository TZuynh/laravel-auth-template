<?php

namespace App\Jobs;

use App\Enums\RenderJobStatus;
use App\Models\RenderJob;
use App\Models\VideoGeneration;
use App\Models\VideoVersion;
use App\Services\Rendering\FFmpegRenderPipeline;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Throwable;

class RenderVideoProjectJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public int $tries = 3;

    public function __construct(private readonly int $renderJobId)
    {
        $this->onQueue(config('ai_video.queue.name', 'render'));
    }

    public function handle(FFmpegRenderPipeline $pipeline): void
    {
        $job = RenderJob::query()->with('videoProject')->find($this->renderJobId);

        if (!$job || !$job->videoProject) {
            return;
        }

        if ($job->status?->isTerminal()) {
            return;
        }

        $job->update([
            'attempts' => $job->attempts + 1,
            'status' => RenderJobStatus::Preparing,
            'started_at' => $job->started_at ?: now(),
            'error_message' => null,
        ]);

        try {
            $export = $pipeline->render($job->videoProject, $job, $job->input_payload ?? []);
            $this->markVersionCompleted($job, $export->file_path);
        } catch (Throwable $exception) {
            $this->markFailed($job, $exception);

            throw $exception;
        }
    }

    public function failed(Throwable $exception): void
    {
        $job = RenderJob::query()->find($this->renderJobId);

        if ($job) {
            $this->markFailed($job, $exception);
        }
    }

    private function markFailed(RenderJob $job, Throwable $exception): void
    {
        $job->update([
            'status' => RenderJobStatus::Failed,
            'current_step' => 'Render failed',
            'error_message' => Str::limit($exception->getMessage(), 2000),
            'finished_at' => now(),
        ]);

        $version = $this->version($job);
        $version?->update([
            'status' => 'failed',
            'progress' => 100,
            'error_message' => Str::limit($exception->getMessage(), 2000),
        ]);

        if ($version?->generation) {
            $this->refreshGenerationStatus($version->generation);
        }
    }

    private function markVersionCompleted(RenderJob $job, ?string $filePath): void
    {
        $version = $this->version($job);
        if (!$version) {
            return;
        }

        $version->update([
            'status' => 'completed',
            'progress' => 100,
            'output_url' => $filePath ? asset('storage/' . ltrim($filePath, '/')) : null,
        ]);

        if ($version->generation) {
            $this->refreshGenerationStatus($version->generation);
        }
    }

    private function version(RenderJob $job): ?VideoVersion
    {
        $versionId = data_get($job->input_payload, 'video_version_id');

        return $versionId
            ? VideoVersion::query()->with('generation.versions')->find($versionId)
            : VideoVersion::query()->with('generation.versions')->where('render_job_id', $job->id)->first();
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
