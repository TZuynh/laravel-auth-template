<?php

namespace App\Jobs;

use App\Services\Rendering\ShotstackService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class RenderShotstackVideoJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    public int $tries = 3;

    public function __construct(
        private readonly array $payload,
        private readonly array $context = [],
    ) {
        $this->onQueue((string) config('shotstack.queue', 'render'));
    }

    public function handle(ShotstackService $shotstack): void
    {
        $renderId = $shotstack->render($this->payload);

        Log::info('Shotstack render queued.', [
            'render_id' => $renderId,
            'context' => $this->context,
        ]);
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Shotstack render job failed.', [
            'message' => $exception->getMessage(),
            'context' => $this->context,
        ]);
    }
}
