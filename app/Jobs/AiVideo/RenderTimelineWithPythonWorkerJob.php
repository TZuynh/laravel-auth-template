<?php

namespace App\Jobs\AiVideo;

use App\Enums\RenderJobStatus;
use App\Models\RenderJob;
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
        } catch (Throwable $exception) {
            $renderJob->update([
                'status' => RenderJobStatus::Failed,
                'current_step' => 'Python AI worker render failed',
                'error_message' => Str::limit($exception->getMessage(), 2000),
                'finished_at' => now(),
            ]);

            throw $exception;
        }
    }
}

