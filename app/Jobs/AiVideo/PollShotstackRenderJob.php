<?php

namespace App\Jobs\AiVideo;

use App\Enums\RenderJobStatus;
use App\Models\RenderJob;
use App\Models\VideoGeneration;
use App\Models\VideoVersion;
use App\Services\Rendering\ShotstackService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;
use Throwable;

class PollShotstackRenderJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 180;

    public int $tries = 3;

    public function __construct(
        private readonly int $renderJobId,
        private readonly int $pollAttempt = 1,
    ) {
    }

    public function handle(ShotstackService $shotstack): void
    {
        $renderJob = RenderJob::query()->find($this->renderJobId);
        if (!$renderJob || $renderJob->status?->isTerminal()) {
            return;
        }

        $renderId = (string) data_get($renderJob->output_payload, 'shotstack_render_id');
        if ($renderId === '') {
            return;
        }

        try {
            $response = $shotstack->status($renderId);
            $status = (string) data_get($response, 'response.status', 'unknown');
            $url = data_get($response, 'response.url');
            $version = $this->version($renderJob);

            if (in_array($status, ['done', 'completed', 'ready'], true) && is_string($url)) {
                $renderJob->update([
                    'status' => RenderJobStatus::Completed,
                    'progress' => 100,
                    'current_step' => 'Shotstack MP4 ready',
                    'output_payload' => array_replace($renderJob->output_payload ?? [], [
                        'shotstack_status' => $status,
                        'url' => $url,
                        'status_response' => $response,
                    ]),
                    'finished_at' => now(),
                ]);

                $version?->update([
                    'status' => 'completed',
                    'progress' => 100,
                    'output_url' => $url,
                ]);

                if ($version?->generation) {
                    $this->refreshGenerationStatus($version->generation);
                }

                return;
            }

            if (in_array($status, ['failed', 'error'], true)) {
                $message = (string) data_get($response, 'response.error', 'Shotstack render failed.');
                $this->markFailed($renderJob, $message, $response);
                return;
            }

            $renderJob->update([
                'progress' => min(95, 40 + ($this->pollAttempt * 5)),
                'current_step' => 'Shotstack status: ' . $status,
                'output_payload' => array_replace($renderJob->output_payload ?? [], [
                    'shotstack_status' => $status,
                    'last_status_response' => $response,
                ]),
            ]);

            if ($this->pollAttempt < (int) config('bulk_ai_video.render.max_poll_attempts', 45)) {
                self::dispatch($renderJob->id, $this->pollAttempt + 1)
                    ->delay(now()->addSeconds((int) config('bulk_ai_video.render.poll_delay_seconds', 20)))
                    ->onQueue((string) config('bulk_ai_video.render.queue', 'render'));
            }
        } catch (Throwable $exception) {
            $this->markFailed($renderJob, $exception->getMessage());
            throw $exception;
        }
    }

    private function markFailed(RenderJob $renderJob, string $message, array $response = []): void
    {
        $renderJob->update([
            'status' => RenderJobStatus::Failed,
            'progress' => 100,
            'current_step' => 'Shotstack render failed',
            'error_message' => Str::limit($message, 2000),
            'output_payload' => array_replace($renderJob->output_payload ?? [], [
                'status_response' => $response,
            ]),
            'finished_at' => now(),
        ]);

        $version = $this->version($renderJob);
        $version?->update([
            'status' => 'failed',
            'progress' => 100,
            'error_message' => Str::limit($message, 2000),
        ]);

        if ($version?->generation) {
            $this->refreshGenerationStatus($version->generation);
        }
    }

    private function version(RenderJob $renderJob): ?VideoVersion
    {
        return VideoVersion::query()
            ->with('generation.versions')
            ->where('render_job_id', $renderJob->id)
            ->first();
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
