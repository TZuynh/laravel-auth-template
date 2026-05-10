<?php

namespace App\Services\Rendering;

use App\Enums\RenderJobStatus;
use App\Enums\RenderJobType;
use App\Jobs\RenderVideoProjectJob;
use App\Models\RenderJob;
use App\Models\VideoProject;
use Illuminate\Support\Str;
use Throwable;

class RenderJobDispatcher
{
    public function __construct(private readonly FFmpegRenderPipeline $pipeline)
    {
    }

    public function dispatch(VideoProject $project, array $options = []): RenderJob
    {
        $renderJob = RenderJob::create([
            'uuid' => (string) Str::uuid(),
            'video_project_id' => $project->id,
            'type' => RenderJobType::RenderFinalVideo,
            'status' => RenderJobStatus::Queued,
            'queue' => config('ai_video.queue.name', 'render'),
            'provider' => 'ffmpeg',
            'progress' => 0,
            'max_attempts' => 3,
            'current_step' => 'Queued for cinematic FFmpeg render',
            'input_payload' => $options,
            'available_at' => now(),
        ]);

        try {
            $this->pipeline->assertAvailable();
        } catch (Throwable $exception) {
            $renderJob->update([
                'status' => RenderJobStatus::Failed,
                'progress' => 0,
                'current_step' => 'FFmpeg preflight failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);

            return $renderJob;
        }

        if (config('ai_video.queue.mode', 'queue') === 'sync') {
            $renderJob->increment('attempts');

            try {
                $this->pipeline->render($project, $renderJob, $options);
            } catch (Throwable $exception) {
                $renderJob->update([
                    'status' => RenderJobStatus::Failed,
                    'current_step' => 'Render failed',
                    'error_message' => Str::limit($exception->getMessage(), 2000),
                    'finished_at' => now(),
                ]);
            }

            return $renderJob->refresh();
        }

        RenderVideoProjectJob::dispatch($renderJob->id);

        return $renderJob;
    }
}
