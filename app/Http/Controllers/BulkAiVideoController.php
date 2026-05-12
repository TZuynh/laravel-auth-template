<?php

namespace App\Http\Controllers;

use App\DTOs\AiVideo\BulkVideoGenerationRequestData;
use App\Http\Requests\AiVideo\StoreBulkVideoGenerationRequest;
use App\Jobs\AiVideo\GenerateBulkVideoVersionJob;
use App\Jobs\AiVideo\PollShotstackRenderJob;
use App\Jobs\AiVideo\RenderVideoVersionWithRemotionJob;
use App\Jobs\AiVideo\RenderVideoVersionWithShotstackJob;
use App\Jobs\RenderVideoProjectJob;
use App\Models\Export;
use App\Models\VideoProject;
use App\Models\VideoGeneration;
use App\Models\VideoVersion;
use App\Services\AiVideo\BulkVideoGenerationService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class BulkAiVideoController extends Controller
{
    public function index(): View
    {
        return view('marketing.bulk-video-generator', [
            'generations' => VideoGeneration::query()
                ->with('versions')
                ->latest('id')
                ->limit(8)
                ->get(),
        ]);
    }

    public function store(StoreBulkVideoGenerationRequest $request, BulkVideoGenerationService $service): RedirectResponse
    {
        $generation = $service->create(
            $request->user(),
            BulkVideoGenerationRequestData::fromValidated($request->validated())
        );

        return redirect()
            ->route('marketing.bulk-video.show', $generation)
            ->with('success', 'AI Video Generator has created 1 video and queued the pipeline.');
    }

    public function show(VideoGeneration $videoGeneration): View
    {
        $this->authorizeGeneration($videoGeneration);

        $videoGeneration->load([
            'versions.videoProject.scenes.sceneAssets',
            'versions.voiceTracks',
            'versions.subtitleTracks',
            'versions.renderJob',
        ]);

        return view('marketing.bulk-video-show', [
            'generation' => $videoGeneration,
        ]);
    }

    public function runNow(VideoGeneration $videoGeneration): RedirectResponse
    {
        $this->authorizeGeneration($videoGeneration);

        $version = $videoGeneration->versions()
            ->whereIn('status', ['queued', 'processing', 'assets_ready'])
            ->oldest('id')
            ->first();

        if (!$version) {
            $renderingVersion = $videoGeneration->versions()
                ->with('renderJob')
                ->where('status', 'rendering')
                ->oldest('id')
                ->first();

            if ($renderingVersion && $this->syncRenderVersion($renderingVersion)) {
                $this->refreshGenerationStatus($videoGeneration->refresh());

                return back()->with('success', 'Render status synced.');
            }

            return back()->with('error', 'No queued or rendering video is available to run now.');
        }

        try {
            dispatch_sync(new GenerateBulkVideoVersionJob($version->id));
            $this->refreshGenerationStatus($videoGeneration->refresh());
        } catch (\Throwable $exception) {
            return back()->with('error', 'Run now failed: ' . $exception->getMessage());
        }

        return back()->with('success', 'Video processing has run now. Refresh the page to see the latest status.');
    }

    public function sync(VideoGeneration $videoGeneration): RedirectResponse
    {
        $this->authorizeGeneration($videoGeneration);
        $videoGeneration->load('versions.renderJob');

        $synced = 0;
        $failed = 0;

        foreach ($videoGeneration->versions as $version) {
            if ($this->syncRenderVersion($version)) {
                $synced++;
                continue;
            }

            if (
                $version->renderJob &&
                !$version->renderJob->status?->isTerminal() &&
                $version->status === 'rendering'
            ) {
                $failed++;
            }
        }

        $this->refreshGenerationStatus($videoGeneration->refresh());

        if ($synced === 0 && $failed === 0) {
            return back()->with('error', 'No active render job found to sync.');
        }

        if ($failed > 0) {
            return back()->with('error', "Synced {$synced} render(s). {$failed} render(s) could not be synced right now.");
        }

        return back()->with('success', "Synced {$synced} render status update(s).");
    }

    public function cancel(VideoGeneration $videoGeneration): RedirectResponse
    {
        $this->authorizeGeneration($videoGeneration);

        DB::transaction(function () use ($videoGeneration): void {
            $videoGeneration->load('versions.renderJob');

            foreach ($videoGeneration->versions as $version) {
                if (!in_array($version->status, ['completed', 'failed'], true)) {
                    $version->update([
                        'status' => 'cancelled',
                        'progress' => 100,
                    ]);
                }

                if ($version->renderJob && !$version->renderJob->status?->isTerminal()) {
                    $version->renderJob->update([
                        'status' => \App\Enums\RenderJobStatus::Cancelled,
                        'progress' => 100,
                        'current_step' => 'Cancelled by user',
                        'finished_at' => now(),
                    ]);
                }
            }

            $videoGeneration->update([
                'status' => 'cancelled',
                'failed_versions' => $videoGeneration->versions->where('status', 'failed')->count(),
            ]);
        });

        return back()->with('success', 'Generation has been cancelled.');
    }

    public function destroy(VideoGeneration $videoGeneration): RedirectResponse
    {
        $this->authorizeGeneration($videoGeneration);

        DB::transaction(function () use ($videoGeneration): void {
            $videoGeneration->load('versions');
            $projectIds = $videoGeneration->versions
                ->pluck('video_project_id')
                ->filter()
                ->unique()
                ->values();

            VideoProject::query()
                ->whereIn('id', $projectIds)
                ->get()
                ->each
                ->delete();

            $videoGeneration->delete();
        });

        return redirect()
            ->route('marketing.bulk-video.index')
            ->with('success', 'Generation has been deleted.');
    }

    private function authorizeGeneration(VideoGeneration $generation): void
    {
        abort_unless(auth()->id() === (int) $generation->user_id || auth()->user()?->role === 'admin', 403);
    }

    private function syncRenderVersion(VideoVersion $version): bool
    {
        $version->loadMissing('renderJob');
        $renderJob = $version->renderJob;

        if (
            !$renderJob ||
            $renderJob->status?->isTerminal() ||
            !in_array($version->status, ['queued', 'assets_ready', 'rendering'], true)
        ) {
            return false;
        }

        try {
            if (($renderJob->provider ?? '') === 'shotstack') {
                $renderId = (string) data_get($renderJob->output_payload, 'shotstack_render_id');

                if ($renderId === '') {
                    dispatch_sync(new RenderVideoVersionWithShotstackJob($renderJob->id));
                    $renderJob->refresh();
                    $renderId = (string) data_get($renderJob->output_payload, 'shotstack_render_id');
                }

                if ($renderId !== '') {
                    dispatch_sync(new PollShotstackRenderJob($renderJob->id, 1));
                } else {
                    return false;
                }

                return true;
            }

            if (($renderJob->provider ?? '') === 'ffmpeg') {
                dispatch_sync(new RenderVideoProjectJob($renderJob->id));
                $this->syncVersionOutputFromExport($version->refresh());

                return true;
            }

            if (($renderJob->provider ?? '') === 'remotion') {
                dispatch_sync(new RenderVideoVersionWithRemotionJob($renderJob->id));
                $this->syncVersionOutputFromExport($version->refresh());

                return true;
            }

            return false;
        } catch (Throwable) {
            return false;
        }
    }

    private function refreshGenerationStatus(VideoGeneration $generation): void
    {
        $generation->load('versions');
        $completed = $generation->versions->where('status', 'completed')->count();
        $failed = $generation->versions->where('status', 'failed')->count();
        $cancelled = $generation->versions->where('status', 'cancelled')->count();
        $running = $generation->versions
            ->whereIn('status', ['queued', 'processing', 'assets_ready', 'rendering'])
            ->count();

        $status = match (true) {
            $running > 0 => 'processing',
            $completed > 0 && $failed === 0 && $cancelled === 0 => 'completed',
            $cancelled > 0 && $completed === 0 && $failed === 0 => 'cancelled',
            $failed > 0 => 'partial',
            default => 'completed',
        };

        $generation->update([
            'completed_versions' => $completed,
            'failed_versions' => $failed,
            'status' => $status,
        ]);
    }

    private function syncVersionOutputFromExport(VideoVersion $version): void
    {
        $version->loadMissing('renderJob');
        $renderJob = $version->renderJob;

        if (!$renderJob) {
            return;
        }

        $exportId = (int) data_get($renderJob->output_payload, 'export_id');
        if ($exportId <= 0) {
            return;
        }

        $export = Export::query()->find($exportId);
        if (!$export || !$export->file_path) {
            return;
        }

        $version->update([
            'output_url' => asset('storage/' . ltrim((string) $export->file_path, '/')),
            'status' => 'completed',
            'progress' => 100,
            'error_message' => null,
        ]);
    }
}
