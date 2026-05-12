<?php

namespace App\Services\Rendering;

use App\Enums\ExportStatus;
use App\Enums\RenderJobStatus;
use App\Models\Export;
use App\Models\RenderJob;
use App\Models\VideoVersion;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class RemotionRenderPipeline
{
    public function __construct(private readonly TimelineBuilder $timelineBuilder)
    {
    }

    public function assertAvailable(): void
    {
        $command = array_merge($this->remotionBinary(), ['--version']);
        $process = new Process($command, base_path());
        $process->setTimeout(20);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                trim($process->getErrorOutput())
                    ?: trim($process->getOutput())
                    ?: 'Remotion is not available. Run npm install and verify node_modules/.bin/remotion exists.'
            );
        }
    }

    public function render(VideoVersion $version, RenderJob $job): Export
    {
        $this->assertAvailable();

        $version->loadMissing(['generation', 'videoProject', 'voiceTracks', 'subtitleTracks']);

        if (!$version->videoProject) {
            throw new RuntimeException('Cannot render Remotion video without a video project.');
        }

        $timeline = $job->input_payload ?: $this->timelineBuilder->buildForVersion($version)->toArray();
        $timeline = $this->prepareTimeline($timeline, $version);
        $workspace = $this->workspace($version, $job);
        $propsPath = $workspace . DIRECTORY_SEPARATOR . 'props.json';
        $timelinePath = $workspace . DIRECTORY_SEPARATOR . 'timeline.json';
        $remotionOutput = $workspace . DIRECTORY_SEPARATOR . 'remotion-render.mp4';

        File::put($timelinePath, $this->json($timeline));
        File::put($propsPath, $this->json([
            'timeline' => array_replace($timeline, [
                'timeline_path' => $this->pathToFileUrl($timelinePath),
            ]),
        ]));

        $format = $this->format((string) ($timeline['aspect_ratio'] ?? $version->aspect_ratio ?: '9:16'));
        $export = $this->createExport($version, $job, $format);

        $this->mark($job, RenderJobStatus::Rendering, 82, 'Rendering cinematic timeline with Remotion', [
            'export_id' => $export->id,
            'timeline_path' => $timelinePath,
            'props_path' => $propsPath,
        ]);

        $this->renderWithRemotion($propsPath, $remotionOutput);

        if (!File::exists($remotionOutput) || File::size($remotionOutput) === 0) {
            throw new RuntimeException('Remotion finished without producing an MP4 file.');
        }

        $finalPath = $this->exportPath($export);
        File::ensureDirectoryExists(dirname($finalPath));

        $this->mark($job, RenderJobStatus::Exporting, 94, 'Optimizing final MP4 with FFmpeg');
        $this->finalEncode($remotionOutput, $finalPath);

        $duration = $this->duration($timeline);
        $export->update([
            'file_path' => $this->publicExportPath($finalPath),
            'file_size' => File::size($finalPath),
            'checksum' => hash_file('sha256', $finalPath),
            'status' => ExportStatus::Ready,
            'duration_seconds' => $duration,
            'metadata' => [
                'pipeline' => 'remotion_ffmpeg',
                'renderer' => 'remotion',
                'final_encoder' => 'ffmpeg',
                'fps' => (int) ($timeline['fps'] ?? config('ai_video.ffmpeg.fps', 30)),
                'scene_count' => count((array) ($timeline['scenes'] ?? [])),
                'composition' => (string) config('ai_video.remotion.composition', 'CinematicVideo'),
            ],
        ]);

        $this->mark($job, RenderJobStatus::Completed, 100, 'Cinematic MP4 export ready', [
            'export_id' => $export->id,
            'file_path' => $export->file_path,
        ]);

        return $export;
    }

    private function renderWithRemotion(string $propsPath, string $outputPath): void
    {
        $command = array_merge($this->remotionBinary(), [
            'render',
            (string) config('ai_video.remotion.entry', base_path('remotion/index.jsx')),
            (string) config('ai_video.remotion.composition', 'CinematicVideo'),
            $outputPath,
            '--props',
            $propsPath,
            '--codec',
            'h264',
            '--pixel-format',
            'yuv420p',
            '--crf',
            (string) config('ai_video.ffmpeg.crf', 20),
            '--x264-preset',
            (string) config('ai_video.ffmpeg.preset', 'veryfast'),
            '--timeout',
            (string) config('ai_video.remotion.frame_timeout', 60000),
            '--concurrency',
            (string) config('ai_video.remotion.concurrency', '50%'),
            '--overwrite',
        ]);

        $browser = trim((string) config('ai_video.remotion.browser_executable', ''));
        if ($browser !== '') {
            $command[] = '--browser-executable';
            $command[] = $browser;
        }

        $this->run($command, (int) config('ai_video.remotion.timeout', 1800), 'Remotion render failed.');
    }

    private function finalEncode(string $inputPath, string $outputPath): void
    {
        $this->run([
            $this->ffmpegBinary(),
            '-y',
            '-i',
            $inputPath,
            '-map',
            '0:v:0',
            '-map',
            '0:a?',
            '-c:v',
            'libx264',
            '-preset',
            (string) config('ai_video.ffmpeg.preset', 'veryfast'),
            '-crf',
            (string) config('ai_video.ffmpeg.crf', 20),
            '-pix_fmt',
            'yuv420p',
            '-movflags',
            '+faststart',
            '-c:a',
            'aac',
            '-b:a',
            '160k',
            '-shortest',
            $outputPath,
        ], (int) config('ai_video.ffmpeg.timeout', 1800), 'FFmpeg final encode failed.');
    }

    private function prepareTimeline(array $timeline, VideoVersion $version): array
    {
        $base = $this->timelineBuilder->buildForVersion($version)->toArray();
        $timeline = array_replace($base, array_filter($timeline, static fn (mixed $value): bool => $value !== null));
        $timeline['title'] = $version->title;
        $timeline['style'] = $version->style_name;
        $timeline['voice'] = $version->voice;
        $timeline['music'] = $version->music;
        $timeline['subtitle_style'] = $version->subtitle_style;
        $timeline['renderer'] = 'remotion';
        $timeline['final_encoder'] = 'ffmpeg';
        $timeline['voice_url'] = $this->mediaSource($timeline['voice_url'] ?? null);
        $timeline['music_url'] = $this->mediaSource($timeline['music_url'] ?? null);

        $timeline['scenes'] = collect((array) ($timeline['scenes'] ?? []))
            ->values()
            ->map(function (array $scene, int $index): array {
                $scene['visual_url'] = $this->mediaSource($scene['visual_url'] ?? null);
                $scene['sort_order'] = $scene['sort_order'] ?? $index + 1;

                return $scene;
            })
            ->all();

        return $timeline;
    }

    private function mediaSource(mixed $source): ?string
    {
        if (!is_string($source) || trim($source) === '') {
            return null;
        }

        $source = trim($source);
        if (str_starts_with($source, 'http://') || str_starts_with($source, 'https://') || str_starts_with($source, 'file://')) {
            $localPath = $this->localPathFromUrl($source);

            return $localPath ? $this->pathToFileUrl($localPath) : $source;
        }

        $localPath = $this->resolveLocalPath($source);

        return $localPath ? $this->pathToFileUrl($localPath) : $source;
    }

    private function localPathFromUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);
        if (!is_string($path) || $path === '') {
            return null;
        }

        $appUrl = trim((string) config('app.url', ''));
        $host = parse_url($appUrl, PHP_URL_HOST);
        $urlHost = parse_url($url, PHP_URL_HOST);
        if ($urlHost && $host && strcasecmp($urlHost, $host) !== 0) {
            return null;
        }

        $path = rawurldecode($path);
        if (str_starts_with($path, '/storage/')) {
            $relative = substr($path, strlen('/storage/'));

            return $this->firstExisting([
                public_path(ltrim($path, '/')),
                storage_path('app/public/' . $relative),
            ]);
        }

        return $this->firstExisting([
            public_path(ltrim($path, '/')),
        ]);
    }

    private function resolveLocalPath(string $path): ?string
    {
        if (preg_match('/^[A-Za-z]:\\\\/', $path) || str_starts_with($path, '/')) {
            return File::exists($path) ? $path : null;
        }

        return $this->firstExisting([
            storage_path('app/public/' . ltrim($path, '/')),
            storage_path('app/' . ltrim($path, '/')),
            public_path(ltrim($path, '/')),
            base_path(ltrim($path, '/')),
        ]);
    }

    private function firstExisting(array $candidates): ?string
    {
        foreach ($candidates as $candidate) {
            if (is_string($candidate) && File::exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function pathToFileUrl(string $path): string
    {
        $normalized = str_replace('\\', '/', $path);
        $normalized = str_replace(' ', '%20', $normalized);

        if (preg_match('/^[A-Za-z]:\//', $normalized)) {
            return 'file:///' . $normalized;
        }

        return 'file://' . $normalized;
    }

    private function remotionBinary(): array
    {
        $localBinary = base_path('node_modules/.bin/' . (PHP_OS_FAMILY === 'Windows' ? 'remotion.cmd' : 'remotion'));
        if (File::exists($localBinary)) {
            return [$localBinary];
        }

        if ((bool) config('ai_video.remotion.allow_npx_download', false)) {
            return [(string) config('ai_video.remotion.npx_binary', PHP_OS_FAMILY === 'Windows' ? 'npx.cmd' : 'npx'), 'remotion'];
        }

        throw new RuntimeException('Remotion dependencies are not installed. Run npm install so node_modules/.bin/remotion is available.');
    }

    private function ffmpegBinary(): string
    {
        $configured = (string) config('ai_video.ffmpeg.binary', 'ffmpeg');

        if ($configured !== 'ffmpeg' && File::exists($configured)) {
            return $configured;
        }

        $toolsPath = storage_path('app/tools');
        if (File::isDirectory($toolsPath)) {
            foreach (File::allFiles($toolsPath) as $file) {
                if (strtolower($file->getFilename()) === 'ffmpeg.exe') {
                    return $file->getRealPath() ?: $file->getPathname();
                }
            }
        }

        return $configured;
    }

    private function createExport(VideoVersion $version, RenderJob $job, array $format): Export
    {
        $export = Export::create([
            'uuid' => (string) Str::uuid(),
            'video_project_id' => $version->video_project_id,
            'render_job_id' => $job->id,
            'aspect_ratio' => $format['aspect_ratio'],
            'format' => 'mp4',
            'resolution_width' => $format['width'],
            'resolution_height' => $format['height'],
            'status' => ExportStatus::Processing,
        ]);

        $job->update(['export_id' => $export->id]);

        return $export;
    }

    private function format(string $aspectRatio): array
    {
        $formats = config('ai_video.formats', []);
        $format = $formats[$aspectRatio] ?? $formats['9:16'] ?? ['width' => 1080, 'height' => 1920];

        return [
            'aspect_ratio' => $aspectRatio,
            'width' => (int) $format['width'],
            'height' => (int) $format['height'],
        ];
    }

    private function duration(array $timeline): float
    {
        return (float) collect((array) ($timeline['scenes'] ?? []))
            ->reduce(fn (float $duration, array $scene): float => max(
                $duration,
                (float) ($scene['start'] ?? 0) + (float) ($scene['duration'] ?? 0)
            ), 0.0);
    }

    private function workspace(VideoVersion $version, RenderJob $job): string
    {
        $path = config('ai_video.workspace.root') . DIRECTORY_SEPARATOR . $version->video_project_id . DIRECTORY_SEPARATOR . $job->uuid . '-remotion';
        File::ensureDirectoryExists($path);

        return $path;
    }

    private function exportPath(Export $export): string
    {
        return config('ai_video.workspace.exports') . DIRECTORY_SEPARATOR . $export->uuid . '.mp4';
    }

    private function publicExportPath(string $absolutePath): string
    {
        return 'ai-video/exports/' . basename($absolutePath);
    }

    private function json(array $payload): string
    {
        return json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }

    private function mark(RenderJob $job, RenderJobStatus $status, int $progress, string $step, array $output = []): void
    {
        $job->update([
            'status' => $status,
            'progress' => min(100, max(0, $progress)),
            'current_step' => $step,
            'output_payload' => array_replace($job->output_payload ?? [], $output),
            'started_at' => $job->started_at ?? now(),
            'finished_at' => $status->isTerminal() ? now() : null,
        ]);
    }

    private function run(array $command, int $timeout, string $fallbackError): void
    {
        $process = new Process($command, base_path());
        $process->setTimeout($timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(trim($process->getErrorOutput()) ?: trim($process->getOutput()) ?: $fallbackError);
        }
    }
}
