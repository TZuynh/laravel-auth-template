<?php

namespace App\Services\Rendering;

use App\Models\Export as VideoExport;
use App\Models\RenderJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class RenderArtifactCleanupService
{
    public function deleteRenderJob(RenderJob $job): int
    {
        $deletedFiles = 0;
        $job->loadMissing('videoProject');

        DB::transaction(function () use ($job, &$deletedFiles): void {
            $exports = VideoExport::query()
                ->where('render_job_id', $job->id)
                ->when($job->export_id, fn ($query) => $query->orWhereKey($job->export_id))
                ->get()
                ->unique('id');

            foreach ($exports as $export) {
                $deletedFiles += $this->deleteExportFile($export);
                $export->delete();
            }

            $this->deleteQueuedPayload($job);
            $job->delete();
        });

        $this->deleteWorkspace($job);

        return $deletedFiles;
    }

    public function deleteExport(VideoExport $export): int
    {
        $deletedFiles = $this->deleteExportFile($export);
        $export->delete();

        return $deletedFiles;
    }

    public function deleteCompletedJobs(): array
    {
        $deletedJobs = 0;
        $deletedFiles = 0;

        RenderJob::query()
            ->whereIn('status', ['completed', 'failed', 'cancelled'])
            ->oldest('id')
            ->chunkById(50, function ($jobs) use (&$deletedJobs, &$deletedFiles): void {
                foreach ($jobs as $job) {
                    $deletedFiles += $this->deleteRenderJob($job);
                    $deletedJobs++;
                }
            });

        return ['jobs' => $deletedJobs, 'files' => $deletedFiles];
    }

    private function deleteExportFile(VideoExport $export): int
    {
        if (!$export->file_path) {
            return 0;
        }

        if (!Storage::disk('public')->exists($export->file_path)) {
            return 0;
        }

        Storage::disk('public')->delete($export->file_path);

        return 1;
    }

    private function deleteQueuedPayload(RenderJob $job): void
    {
        if (!Schema::hasTable('jobs')) {
            return;
        }

        DB::table('jobs')
            ->where('queue', $job->queue ?: config('ai_video.queue.name', 'render'))
            ->where('payload', 'like', '%renderJobId%')
            ->where('payload', 'like', '%i:' . $job->id . ';%')
            ->delete();
    }

    private function deleteWorkspace(RenderJob $job): void
    {
        if (!$job->uuid || !$job->video_project_id) {
            return;
        }

        $root = config('ai_video.workspace.root');
        $target = $root . DIRECTORY_SEPARATOR . $job->video_project_id . DIRECTORY_SEPARATOR . $job->uuid;

        if (!File::isDirectory($target)) {
            return;
        }

        $rootReal = realpath($root);
        $targetReal = realpath($target);

        if ($rootReal === false || $targetReal === false) {
            return;
        }

        $rootPrefix = rtrim(strtolower($rootReal), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!str_starts_with(strtolower($targetReal), $rootPrefix)) {
            return;
        }

        File::deleteDirectory($targetReal);
    }
}
