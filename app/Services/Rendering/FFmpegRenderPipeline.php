<?php

namespace App\Services\Rendering;

use App\Enums\ExportStatus;
use App\Enums\RenderJobStatus;
use App\Models\Export;
use App\Models\RenderJob;
use App\Models\SceneAsset;
use App\Models\VideoProject;
use App\Models\VideoScene;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

class FFmpegRenderPipeline
{
    public function assertAvailable(): void
    {
        $process = new Process([$this->binary(), '-version']);
        $process->setTimeout(10);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(
                'FFmpeg is not available. Install FFmpeg and set FFMPEG_BINARY in .env, or add ffmpeg.exe to PATH.'
            );
        }
    }

    public function render(VideoProject $project, RenderJob $job, array $options = []): Export
    {
        $this->assertAvailable();

        $format = $this->format($options['aspect_ratio'] ?? ($project->aspect_ratio?->value ?? '9:16'));
        $workspace = $this->workspace($project, $job);
        $sceneClips = [];

        $this->mark($job, RenderJobStatus::Preparing, 5, 'Preparing render workspace');

        $scenes = $project->scenes()->with(['sceneAssets', 'subtitles'])->get();
        if ($scenes->isEmpty()) {
            throw new RuntimeException('Cannot render a video project without scenes.');
        }

        foreach ($scenes as $index => $scene) {
            $this->mark($job, RenderJobStatus::Rendering, 10 + ($index * 12), "Rendering scene {$scene->sort_order}");

            $asset = $scene->sceneAssets
                ->first(fn (SceneAsset $sceneAsset): bool => in_array((string) $sceneAsset->type?->value, ['generated_video', 'generated_image'], true));
            $sourceImagePath = $this->resolveSceneSourceImage(
                $asset?->path ?? $this->productImagePath($project),
                $workspace,
                (int) $scene->sort_order
            );
            $sceneImagePath = $this->composeSceneImage(
                project: $project,
                scene: $scene,
                sourceImagePath: $sourceImagePath,
                workspace: $workspace,
                width: $format['width'],
                height: $format['height']
            );

            $clipPath = $workspace . DIRECTORY_SEPARATOR . sprintf('scene_%02d.mp4', $scene->sort_order);
            $this->renderSceneClip(
                inputPath: $sceneImagePath,
                outputPath: $clipPath,
                width: $format['width'],
                height: $format['height'],
                duration: (float) $scene->duration_seconds,
                subtitle: (string) ($scene->subtitle_text ?: $scene->voice_over_text ?: $scene->title)
            );

            $sceneClips[] = $clipPath;
        }

        $mergedPath = $workspace . DIRECTORY_SEPARATOR . 'merged-scenes.mp4';
        $this->mark($job, RenderJobStatus::Rendering, 72, 'Merging scene clips');
        $this->mergeScenes($sceneClips, $mergedPath, $workspace);

        $gradedPath = $workspace . DIRECTORY_SEPARATOR . 'graded.mp4';
        $this->mark($job, RenderJobStatus::Exporting, 84, 'Applying cinematic color grade');
        $this->colorGrade($mergedPath, $gradedPath);

        $export = $this->createExport($project, $job, $format);
        $finalPath = $this->exportPath($export);
        $duration = (float) $scenes->sum(fn ($scene): float => (float) $scene->duration_seconds);
        $audioPath = $this->createAudioTrack($project, $scenes, $workspace, $duration);

        File::ensureDirectoryExists(dirname($finalPath));
        if ($audioPath) {
            $this->muxAudio($gradedPath, $audioPath, $finalPath, $duration);
        } else {
            File::move($gradedPath, $finalPath);
        }

        $export->update([
            'file_path' => $this->publicExportPath($finalPath),
            'file_size' => File::size($finalPath),
            'checksum' => hash_file('sha256', $finalPath),
            'status' => ExportStatus::Ready,
            'duration_seconds' => $duration,
            'metadata' => [
                'pipeline' => 'ffmpeg',
                'fps' => config('ai_video.ffmpeg.fps'),
                'scene_count' => $scenes->count(),
                'has_audio' => (bool) $audioPath,
                'audio_source' => $audioPath ? basename($audioPath) : null,
            ],
        ]);

        $this->mark($job, RenderJobStatus::Completed, 100, 'MP4 export ready', ['export_id' => $export->id]);

        return $export;
    }

    private function productImagePath(VideoProject $project): ?string
    {
        $project->loadMissing(['product.assets']);

        $asset = $project->product?->assets
            ?->sortByDesc(fn ($productAsset): int => $productAsset->is_primary ? 1 : 0)
            ->first();

        return $asset?->path ?: $project->product?->image;
    }

    private function resolveSceneSourceImage(?string $path, string $workspace, int $sceneOrder): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $this->downloadRemoteImage($path, $workspace, $sceneOrder);
        }

        return $this->resolveMediaPath($path);
    }

    private function downloadRemoteImage(string $url, string $workspace, int $sceneOrder): ?string
    {
        try {
            $response = Http::timeout(20)->retry(1, 250)->get($url);

            if (!$response->ok() || !str_starts_with((string) $response->header('Content-Type'), 'image/')) {
                return null;
            }

            $path = $workspace . DIRECTORY_SEPARATOR . sprintf('remote_product_%02d.jpg', $sceneOrder);
            File::put($path, $response->body());

            return $path;
        } catch (\Throwable) {
            return null;
        }
    }

    private function composeSceneImage(
        VideoProject $project,
        VideoScene $scene,
        ?string $sourceImagePath,
        string $workspace,
        int $width,
        int $height
    ): string {
        if (!extension_loaded('gd')) {
            return $sourceImagePath ?: $this->createFlatFallbackImage($project, $scene, $workspace, $width, $height);
        }

        $image = imagecreatetruecolor($width, $height);
        imageantialias($image, true);
        imagealphablending($image, true);
        $this->paintGradientBackground($image, $width, $height);
        $this->paintLightRays($image, $width, $height);
        $this->paintProductStage($image, $project, $scene, $sourceImagePath, $width, $height);
        $this->paintCharacter($image, $project, $width, $height);
        $this->paintSceneTypography($image, $project, $scene, $width, $height);

        $path = $workspace . DIRECTORY_SEPARATOR . sprintf('composed_scene_%02d.png', $scene->sort_order);
        imagepng($image, $path, 6);
        imagedestroy($image);

        return $path;
    }

    private function createFlatFallbackImage(VideoProject $project, VideoScene $scene, string $workspace, int $width, int $height): string
    {
        $path = $workspace . DIRECTORY_SEPARATOR . sprintf('fallback_scene_%02d.png', $scene->sort_order);
        $this->run([
            $this->binary(),
            '-y',
            '-f',
            'lavfi',
            '-i',
            "color=c=0x13111f:s={$width}x{$height}:d=0.1",
            '-frames:v',
            '1',
            $path,
        ]);

        return $path;
    }

    private function paintGradientBackground(\GdImage $image, int $width, int $height): void
    {
        $top = [15, 18, 38];
        $middle = [75, 46, 90];
        $bottom = [20, 23, 44];

        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / max(1, $height - 1);
            if ($ratio < 0.55) {
                $mix = $ratio / 0.55;
                $color = $this->mixRgb($top, $middle, $mix);
            } else {
                $mix = ($ratio - 0.55) / 0.45;
                $color = $this->mixRgb($middle, $bottom, $mix);
            }

            imageline($image, 0, $y, $width, $y, imagecolorallocate($image, $color[0], $color[1], $color[2]));
        }
    }

    private function paintLightRays(\GdImage $image, int $width, int $height): void
    {
        imagefilledellipse($image, (int) ($width * 0.64), (int) ($height * 0.22), (int) ($width * 0.42), (int) ($height * 0.44), imagecolorallocatealpha($image, 245, 210, 122, 95));
        imagefilledellipse($image, (int) ($width * 0.25), (int) ($height * 0.28), (int) ($width * 0.38), (int) ($height * 0.34), imagecolorallocatealpha($image, 59, 130, 246, 105));
        imagefilledellipse($image, (int) ($width * 0.74), (int) ($height * 0.72), (int) ($width * 0.30), (int) ($height * 0.24), imagecolorallocatealpha($image, 236, 72, 153, 108));

        $line = imagecolorallocatealpha($image, 255, 255, 255, 116);
        for ($i = 0; $i < 10; $i++) {
            $x = (int) ($width * (0.12 + $i * 0.09));
            imageline($image, $x, 0, (int) ($x + $width * 0.18), $height, $line);
        }
    }

    private function paintProductStage(\GdImage $image, VideoProject $project, VideoScene $scene, ?string $sourceImagePath, int $width, int $height): void
    {
        $cardX = (int) ($width * 0.43);
        $cardY = (int) ($height * 0.13);
        $cardW = (int) ($width * 0.40);
        $cardH = (int) ($height * 0.64);

        imagefilledrectangle($image, $cardX + 28, $cardY + 34, $cardX + $cardW + 28, $cardY + $cardH + 34, imagecolorallocatealpha($image, 0, 0, 0, 80));
        imagefilledrectangle($image, $cardX, $cardY, $cardX + $cardW, $cardY + $cardH, imagecolorallocate($image, 242, 238, 225));
        imagefilledrectangle($image, $cardX + 18, $cardY + 18, $cardX + $cardW - 18, $cardY + $cardH - 18, imagecolorallocate($image, 18, 20, 35));

        if ($sourceImagePath && File::exists($sourceImagePath)) {
            $source = @imagecreatefromstring((string) File::get($sourceImagePath));
            if ($source instanceof \GdImage) {
                $this->copyCover($source, $image, $cardX + 26, $cardY + 26, $cardW - 52, $cardH - 52);
                imagedestroy($source);
                return;
            }
        }

        $posterX = $cardX + 26;
        $posterY = $cardY + 26;
        $posterW = $cardW - 52;
        $posterH = $cardH - 52;
        imagefilledrectangle($image, $posterX, $posterY, $posterX + $posterW, $posterY + $posterH, imagecolorallocate($image, 70, 44, 96));
        imagefilledellipse($image, (int) ($posterX + $posterW * 0.46), (int) ($posterY + $posterH * 0.40), (int) ($posterW * 0.58), (int) ($posterH * 0.34), imagecolorallocatealpha($image, 245, 197, 66, 18));
        imagefilledellipse($image, (int) ($posterX + $posterW * 0.62), (int) ($posterY + $posterH * 0.34), (int) ($posterW * 0.40), (int) ($posterH * 0.18), imagecolorallocatealpha($image, 18, 18, 24, 48));
        imagefilledellipse($image, (int) ($posterX + $posterW * 0.35), (int) ($posterY + $posterH * 0.33), (int) ($posterW * 0.14), (int) ($posterH * 0.10), imagecolorallocatealpha($image, 12, 12, 18, 55));
        $this->drawWrappedText($image, $this->shortProductName($project), $posterX + 32, $posterY + $posterH - 160, $posterW - 64, 28, imagecolorallocate($image, 255, 255, 255), $this->font(true), 2);
    }

    private function paintCharacter(\GdImage $image, VideoProject $project, int $width, int $height): void
    {
        $settings = $project->settings ?? [];
        $character = $settings['character'] ?? 'presenter';

        if ($character === 'none') {
            return;
        }

        $gender = $settings['gender'] ?? 'female';
        $cx = (int) ($width * 0.25);
        $baseY = (int) ($height * 0.82);
        $skin = imagecolorallocate($image, 238, 191, 154);
        $shadow = imagecolorallocatealpha($image, 0, 0, 0, 72);
        $hair = $gender === 'female' ? imagecolorallocate($image, 38, 26, 22) : imagecolorallocate($image, 22, 26, 33);
        $jacket = imagecolorallocate($image, 30, 41, 70);
        $shirt = imagecolorallocate($image, 235, 239, 246);

        imagefilledellipse($image, $cx, $baseY + 16, (int) ($width * 0.19), (int) ($height * 0.05), $shadow);
        imagefilledpolygon($image, [
            $cx - (int) ($width * 0.12), $baseY,
            $cx - (int) ($width * 0.06), (int) ($height * 0.52),
            $cx + (int) ($width * 0.06), (int) ($height * 0.52),
            $cx + (int) ($width * 0.13), $baseY,
        ], 4, $jacket);
        imagefilledpolygon($image, [
            $cx - (int) ($width * 0.035), (int) ($height * 0.54),
            $cx, (int) ($height * 0.68),
            $cx + (int) ($width * 0.035), (int) ($height * 0.54),
        ], 3, $shirt);
        imagefilledellipse($image, $cx, (int) ($height * 0.42), (int) ($height * 0.17), (int) ($height * 0.19), $skin);

        if ($gender === 'female') {
            imagefilledellipse($image, $cx, (int) ($height * 0.39), (int) ($height * 0.22), (int) ($height * 0.22), $hair);
            imagefilledellipse($image, $cx, (int) ($height * 0.43), (int) ($height * 0.16), (int) ($height * 0.18), $skin);
        } else {
            imagefilledellipse($image, $cx, (int) ($height * 0.36), (int) ($height * 0.16), (int) ($height * 0.07), $hair);
        }

        imagefilledellipse($image, $cx - (int) ($height * 0.032), (int) ($height * 0.42), 8, 8, imagecolorallocate($image, 20, 24, 34));
        imagefilledellipse($image, $cx + (int) ($height * 0.032), (int) ($height * 0.42), 8, 8, imagecolorallocate($image, 20, 24, 34));
        imagearc($image, $cx, (int) ($height * 0.455), 70, 34, 10, 170, imagecolorallocate($image, 120, 54, 62));

        $this->drawWrappedText($image, $character === 'customer' ? 'CUSTOMER STORY' : 'AI PRESENTER', (int) ($width * 0.13), (int) ($height * 0.84), (int) ($width * 0.26), 22, imagecolorallocate($image, 219, 234, 254), $this->font(true), 1);
    }

    private function paintSceneTypography(\GdImage $image, VideoProject $project, VideoScene $scene, int $width, int $height): void
    {
        $white = imagecolorallocate($image, 255, 255, 255);
        $blue = imagecolorallocate($image, 191, 219, 254);
        $muted = imagecolorallocate($image, 226, 232, 240);
        $fontBold = $this->font(true);
        $font = $this->font(false);

        imagettftext($image, max(18, (int) ($height * 0.026)), 0, (int) ($width * 0.055), (int) ($height * 0.12), $blue, $fontBold, 'AI CINEMATIC AD');
        $this->drawWrappedText($image, Str::upper((string) $scene->title), (int) ($width * 0.055), (int) ($height * 0.19), (int) ($width * 0.34), max(34, (int) ($height * 0.055)), $white, $fontBold, 2);
        $this->drawWrappedText($image, $this->shortProductName($project), (int) ($width * 0.055), (int) ($height * 0.34), (int) ($width * 0.32), max(18, (int) ($height * 0.026)), $muted, $font, 3);
    }

    private function renderSceneClip(?string $inputPath, string $outputPath, int $width, int $height, float $duration, string $subtitle): void
    {
        $fps = (string) config('ai_video.ffmpeg.fps', 30);
        $subtitleFilter = $this->subtitleFilter($subtitle, $height);
        $vf = "scale={$width}:{$height}:force_original_aspect_ratio=increase,crop={$width}:{$height},zoompan=z='min(zoom+0.0018,1.08)':d=1:s={$width}x{$height}:fps={$fps},{$subtitleFilter},format=yuv420p";

        $resolvedInputPath = $this->resolveMediaPath($inputPath);

        if ($resolvedInputPath) {
            $command = [
                $this->binary(),
                '-y',
                '-loop',
                '1',
                '-i',
                $resolvedInputPath,
                '-t',
                (string) max($duration, 1),
                '-vf',
                $vf,
                '-an',
                '-c:v',
                'libx264',
                '-preset',
                config('ai_video.ffmpeg.preset', 'veryfast'),
                '-crf',
                (string) config('ai_video.ffmpeg.crf', 20),
                $outputPath,
            ];
        } else {
            $command = [
                $this->binary(),
                '-y',
                '-f',
                'lavfi',
                '-i',
                "color=c=0x020617:s={$width}x{$height}:d=" . max($duration, 1),
                '-vf',
                "fade=t=in:st=0:d=0.25,fade=t=out:st=" . max($duration - 0.35, 0.1) . ":d=0.25,{$subtitleFilter},format=yuv420p",
                '-an',
                '-c:v',
                'libx264',
                '-preset',
                config('ai_video.ffmpeg.preset', 'veryfast'),
                '-crf',
                (string) config('ai_video.ffmpeg.crf', 20),
                $outputPath,
            ];
        }

        $this->run($command);
    }

    private function mergeScenes(array $sceneClips, string $outputPath, string $workspace): void
    {
        $concatFile = $workspace . DIRECTORY_SEPARATOR . 'concat.txt';
        File::put($concatFile, collect($sceneClips)
            ->map(fn (string $path): string => "file '" . str_replace("'", "'\\''", str_replace('\\', '/', $path)) . "'")
            ->implode(PHP_EOL));

        $this->run([
            $this->binary(),
            '-y',
            '-f',
            'concat',
            '-safe',
            '0',
            '-i',
            $concatFile,
            '-c',
            'copy',
            $outputPath,
        ]);
    }

    private function resolveMediaPath(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $candidates = [
            $path,
            storage_path('app/' . ltrim($path, '/')),
            storage_path('app/public/' . ltrim($path, '/')),
            public_path(ltrim($path, '/')),
        ];

        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function subtitleFilter(string $subtitle, int $height): string
    {
        $text = str_replace(["\\", "'", ':', ',', '%', '[', ']', "\r", "\n"], ['\\\\', "\\'", '\\:', '\\,', '\\%', '\\[', '\\]', ' ', ' '], $subtitle);
        $fontSize = max(36, (int) round($height * 0.036));
        $y = (int) round($height * 0.78);

        return "drawtext=text='{$text}':fontcolor=white:fontsize={$fontSize}:x=(w-text_w)/2:y={$y}:box=1:boxcolor=black@0.42:boxborderw=28";
    }

    private function createAudioTrack(VideoProject $project, $scenes, string $workspace, float $duration): ?string
    {
        $settings = $project->settings ?? [];
        $voiceChoice = (string) ($settings['voice'] ?? 'female_south');
        $musicChoice = (string) ($settings['music'] ?? 'tiktok');
        $useReferenceAudio = (bool) config('ai_video.audio.use_reference', false)
            || in_array($voiceChoice, ['reference', 'reference_audio'], true);

        $referenceAudio = $useReferenceAudio ? $this->referenceAudioPath() : null;
        if ($referenceAudio) {
            $output = $workspace . DIRECTORY_SEPARATOR . 'reference_audio.m4a';
            $this->fitAudioToDuration($referenceAudio, $output, $duration);

            return $output;
        }

        $voicePath = $voiceChoice === 'none' ? null : $this->createLocalVoiceover($project, $scenes, $workspace, $voiceChoice);
        $musicPath = $musicChoice === 'none' ? null : $this->createMusicBed($workspace, $duration, $musicChoice);

        if ($voicePath && $musicPath) {
            $mixedPath = $workspace . DIRECTORY_SEPARATOR . 'voice_music_mix.m4a';
            $this->mixVoiceAndMusic($voicePath, $musicPath, $mixedPath, $duration);

            return $mixedPath;
        }

        if ($voicePath || $musicPath) {
            return $voicePath ?: $musicPath;
        }

        return $this->createMusicBed($workspace, $duration, 'tiktok');
    }

    private function referenceAudioPath(): ?string
    {
        $configured = (string) config('ai_video.audio.reference_path', '');
        $candidates = array_filter([
            $configured,
            storage_path('app/ai-video/reference/reference-audio.m4a'),
            storage_path('app/ai-video/reference/reference-audio.mp3'),
            storage_path('app/ai-video/reference/reference-audio.mp4'),
        ]);

        foreach ($candidates as $candidate) {
            if (File::exists($candidate) && File::size($candidate) > 0) {
                return $candidate;
            }
        }

        return null;
    }

    private function fitAudioToDuration(string $inputPath, string $outputPath, float $duration): void
    {
        $this->run([
            $this->binary(),
            '-y',
            '-stream_loop',
            '-1',
            '-i',
            $inputPath,
            '-t',
            (string) max($duration, 1),
            '-vn',
            '-af',
            'afade=t=in:st=0:d=0.25,afade=t=out:st=' . max($duration - 0.45, 0.1) . ':d=0.35,volume=0.92',
            '-c:a',
            'aac',
            '-b:a',
            '160k',
            $outputPath,
        ]);
    }

    private function createLocalVoiceover(VideoProject $project, $scenes, string $workspace, string $voiceChoice): ?string
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return null;
        }

        $text = $scenes
            ->map(fn (VideoScene $scene): string => trim((string) ($scene->voice_over_text ?: $scene->subtitle_text)))
            ->filter()
            ->implode(' ');

        if ($text === '') {
            return null;
        }

        $textPath = $workspace . DIRECTORY_SEPARATOR . 'voiceover.txt';
        $voicePath = $workspace . DIRECTORY_SEPARATOR . 'voiceover.wav';
        File::put($textPath, $text);

        $voice = $this->voiceSettings($voiceChoice);
        $script = '& { param($textPath, $wavPath, $voiceGender, $voiceLanguage, $rate) Add-Type -AssemblyName System.Speech; $speaker = New-Object System.Speech.Synthesis.SpeechSynthesizer; $speaker.Rate = [int]$rate; $speaker.Volume = 100; $voices = $speaker.GetInstalledVoices() | Where-Object { $_.Enabled }; $match = $voices | Where-Object { ($_.VoiceInfo.Gender.ToString().ToLowerInvariant() -eq $voiceGender) -and ($_.VoiceInfo.Culture.Name -like "$voiceLanguage*") } | Select-Object -First 1; if (-not $match) { $match = $voices | Where-Object { $_.VoiceInfo.Gender.ToString().ToLowerInvariant() -eq $voiceGender } | Select-Object -First 1; } if ($match) { $speaker.SelectVoice($match.VoiceInfo.Name); } $speaker.SetOutputToWaveFile($wavPath); $speaker.Speak([System.IO.File]::ReadAllText($textPath)); $speaker.Dispose(); }';
        $process = new Process(['powershell', '-NoProfile', '-ExecutionPolicy', 'Bypass', '-Command', $script, $textPath, $voicePath, $voice['gender'], $voice['language'], (string) $voice['rate']]);
        $process->setTimeout(120);
        $process->run();

        if (!$process->isSuccessful() || !File::exists($voicePath) || File::size($voicePath) === 0) {
            if (File::exists($voicePath)) {
                File::delete($voicePath);
            }

            return null;
        }

        return $voicePath;
    }

    private function createMusicBed(string $workspace, float $duration, string $musicChoice = 'tiktok'): string
    {
        $outputPath = $workspace . DIRECTORY_SEPARATOR . 'music_bed.m4a';
        $fadeOut = max($duration - 0.6, 0.1);
        $music = $this->musicSettings($musicChoice);

        $this->run([
            $this->binary(),
            '-y',
            '-f',
            'lavfi',
            '-i',
            'sine=frequency=' . $music['low'] . ':sample_rate=48000:duration=' . max($duration, 1),
            '-f',
            'lavfi',
            '-i',
            'sine=frequency=' . $music['high'] . ':sample_rate=48000:duration=' . max($duration, 1),
            '-filter_complex',
            "[0:a][1:a]amix=inputs=2:duration=longest,volume={$music['volume']},afade=t=in:st=0:d=0.35,afade=t=out:st={$fadeOut}:d=0.45",
            '-c:a',
            'aac',
            '-b:a',
            '128k',
            $outputPath,
        ]);

        return $outputPath;
    }

    private function mixVoiceAndMusic(string $voicePath, string $musicPath, string $outputPath, float $duration): void
    {
        $this->run([
            $this->binary(),
            '-y',
            '-i',
            $voicePath,
            '-i',
            $musicPath,
            '-filter_complex',
            '[0:a]volume=1.0[a0];[1:a]volume=0.36[a1];[a0][a1]amix=inputs=2:duration=longest:dropout_transition=0,loudnorm=I=-16:TP=-1.5:LRA=11[a]',
            '-map',
            '[a]',
            '-t',
            (string) max($duration, 1),
            '-c:a',
            'aac',
            '-b:a',
            '160k',
            $outputPath,
        ]);
    }

    private function voiceSettings(string $voiceChoice): array
    {
        return match ($voiceChoice) {
            'male_north' => ['gender' => 'male', 'language' => 'vi', 'rate' => -1],
            'ai_en' => ['gender' => 'female', 'language' => 'en', 'rate' => 0],
            default => ['gender' => 'female', 'language' => 'vi', 'rate' => 0],
        };
    }

    private function musicSettings(string $musicChoice): array
    {
        return match ($musicChoice) {
            'epic' => ['low' => 110, 'high' => 220, 'volume' => 0.20],
            'lofi' => ['low' => 174, 'high' => 261, 'volume' => 0.14],
            'funny' => ['low' => 330, 'high' => 660, 'volume' => 0.12],
            default => ['low' => 196, 'high' => 392, 'volume' => 0.18],
        };
    }

    private function muxAudio(string $videoPath, string $audioPath, string $outputPath, float $duration): void
    {
        $this->run([
            $this->binary(),
            '-y',
            '-i',
            $videoPath,
            '-i',
            $audioPath,
            '-t',
            (string) max($duration, 1),
            '-map',
            '0:v:0',
            '-map',
            '1:a:0',
            '-c:v',
            'copy',
            '-c:a',
            'aac',
            '-b:a',
            '160k',
            '-shortest',
            $outputPath,
        ]);
    }

    private function copyCover(\GdImage $source, \GdImage $target, int $dstX, int $dstY, int $dstW, int $dstH): void
    {
        $srcW = imagesx($source);
        $srcH = imagesy($source);
        $srcRatio = $srcW / max(1, $srcH);
        $dstRatio = $dstW / max(1, $dstH);

        if ($srcRatio > $dstRatio) {
            $cropH = $srcH;
            $cropW = (int) round($srcH * $dstRatio);
            $srcX = (int) round(($srcW - $cropW) / 2);
            $srcY = 0;
        } else {
            $cropW = $srcW;
            $cropH = (int) round($srcW / $dstRatio);
            $srcX = 0;
            $srcY = (int) round(($srcH - $cropH) / 2);
        }

        imagecopyresampled($target, $source, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $cropW, $cropH);
    }

    private function drawWrappedText(\GdImage $image, string $text, int $x, int $y, int $maxWidth, int $fontSize, int $color, string $font, int $maxLines = 3): void
    {
        $words = preg_split('/\s+/u', trim($text)) ?: [];
        $lines = [];
        $line = '';

        foreach ($words as $word) {
            $candidate = trim($line . ' ' . $word);
            $box = imagettfbbox($fontSize, 0, $font, $candidate);
            $candidateWidth = $box ? abs($box[2] - $box[0]) : 0;

            if ($candidateWidth > $maxWidth && $line !== '') {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $candidate;
            }

            if (count($lines) >= $maxLines) {
                break;
            }
        }

        if ($line !== '' && count($lines) < $maxLines) {
            $lines[] = $line;
        }

        foreach (array_slice($lines, 0, $maxLines) as $index => $lineText) {
            if ($index === $maxLines - 1 && count($words) > count(preg_split('/\s+/u', implode(' ', $lines)) ?: [])) {
                $lineText = rtrim($lineText, '.,') . '...';
            }

            imagettftext($image, $fontSize, 0, $x, $y + ($index * (int) round($fontSize * 1.28)), $color, $font, $lineText);
        }
    }

    private function font(bool $bold = false): string
    {
        $candidates = $bold
            ? ['C:\Windows\Fonts\arialbd.ttf', 'C:\Windows\Fonts\segoeuib.ttf']
            : ['C:\Windows\Fonts\arial.ttf', 'C:\Windows\Fonts\segoeui.ttf'];

        foreach ($candidates as $candidate) {
            if (File::exists($candidate)) {
                return $candidate;
            }
        }

        return $candidates[0];
    }

    private function shortProductName(VideoProject $project): string
    {
        $name = $project->product?->name ?: $project->title;

        return Str::limit($name, 92);
    }

    private function mixRgb(array $from, array $to, float $ratio): array
    {
        $ratio = max(0, min(1, $ratio));

        return [
            (int) round($from[0] + (($to[0] - $from[0]) * $ratio)),
            (int) round($from[1] + (($to[1] - $from[1]) * $ratio)),
            (int) round($from[2] + (($to[2] - $from[2]) * $ratio)),
        ];
    }

    private function colorGrade(string $inputPath, string $outputPath): void
    {
        $this->run([
            $this->binary(),
            '-y',
            '-i',
            $inputPath,
            '-vf',
            'eq=contrast=1.08:saturation=1.12:brightness=-0.015,unsharp=5:5:0.45',
            '-c:v',
            'libx264',
            '-preset',
            config('ai_video.ffmpeg.preset', 'veryfast'),
            '-crf',
            (string) config('ai_video.ffmpeg.crf', 20),
            '-pix_fmt',
            'yuv420p',
            $outputPath,
        ]);
    }

    private function createExport(VideoProject $project, RenderJob $job, array $format): Export
    {
        return Export::create([
            'uuid' => (string) Str::uuid(),
            'video_project_id' => $project->id,
            'render_job_id' => $job->id,
            'aspect_ratio' => $format['aspect_ratio'],
            'format' => 'mp4',
            'resolution_width' => $format['width'],
            'resolution_height' => $format['height'],
            'status' => ExportStatus::Processing,
        ]);
    }

    private function format(string $aspectRatio): array
    {
        $formats = config('ai_video.formats', []);
        $format = $formats[$aspectRatio] ?? $formats['9:16'];

        return [
            'aspect_ratio' => $aspectRatio,
            'width' => (int) $format['width'],
            'height' => (int) $format['height'],
        ];
    }

    private function workspace(VideoProject $project, RenderJob $job): string
    {
        $path = config('ai_video.workspace.root') . DIRECTORY_SEPARATOR . $project->id . DIRECTORY_SEPARATOR . $job->uuid;
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

    private function run(array $command): void
    {
        $process = new Process($command);
        $process->setTimeout((int) config('ai_video.ffmpeg.timeout', 1800));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException(trim($process->getErrorOutput()) ?: trim($process->getOutput()) ?: 'FFmpeg command failed.');
        }
    }

    private function binary(): string
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
}
