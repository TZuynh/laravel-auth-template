<?php

namespace App\Jobs;

use App\Enums\RenderJobStatus;
use App\Models\RenderJob;
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
            $pipeline->render($job->videoProject, $job, $job->input_payload ?? []);
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
    }
}
