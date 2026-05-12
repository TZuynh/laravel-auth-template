<?php

namespace App\Jobs\AiVideo;

use App\Enums\RenderJobStatus;
use App\Models\RenderJob;
use App\Models\VideoVersion;
use App\Services\Rendering\ShotstackService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Throwable;

class RenderVideoVersionWithShotstackJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public int $tries = 3;

    public function __construct(private readonly int $renderJobId)
    {
    }

    public function handle(ShotstackService $shotstack): void
    {
        $renderJob = RenderJob::query()->find($this->renderJobId);
        if (!$renderJob || $renderJob->status?->isTerminal()) {
            return;
        }

        $renderJob->update([
            'attempts' => $renderJob->attempts + 1,
            'status' => RenderJobStatus::Rendering,
            'progress' => 15,
            'current_step' => 'Submitting timeline to Shotstack',
            'started_at' => $renderJob->started_at ?: now(),
        ]);

        try {
            $renderId = $shotstack->render($renderJob->input_payload ?? []);

            $renderJob->update([
                'progress' => 40,
                'current_step' => 'Shotstack render queued',
                'output_payload' => array_replace($renderJob->output_payload ?? [], [
                    'shotstack_render_id' => $renderId,
                ]),
            ]);

            $this->version($renderJob)?->update([
                'status' => 'rendering',
                'progress' => 85,
            ]);

            PollShotstackRenderJob::dispatch($renderJob->id, 1)
                ->delay(now()->addSeconds((int) config('bulk_ai_video.render.poll_delay_seconds', 20)))
                ->onQueue((string) config('bulk_ai_video.render.queue', 'render'));
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
        $renderJob->update([
            'status' => RenderJobStatus::Failed,
            'progress' => 100,
            'current_step' => 'Shotstack render submission failed',
            'error_message' => Str::limit($exception->getMessage(), 2000),
            'finished_at' => now(),
        ]);

        $this->version($renderJob)?->update([
            'status' => 'failed',
            'progress' => 100,
            'error_message' => Str::limit($exception->getMessage(), 2000),
        ]);
    }

    private function version(RenderJob $renderJob): ?VideoVersion
    {
        return VideoVersion::query()->where('render_job_id', $renderJob->id)->first();
    }
}
