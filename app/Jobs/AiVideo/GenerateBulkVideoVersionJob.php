<?php

namespace App\Jobs\AiVideo;

use App\Enums\AiPromptStatus;
use App\Enums\AiPromptType;
use App\Enums\AssetStatus;
use App\Enums\AssetType;
use App\Enums\VideoProjectStatus;
use App\Enums\VideoSceneStatus;
use App\Models\AiPrompt;
use App\Models\SceneAsset;
use App\Models\SubtitleTrack;
use App\Models\Transition;
use App\Models\VideoGeneration;
use App\Models\VideoVersion;
use App\Models\VoiceTrack;
use App\Services\AI\AiProviderManager;
use App\Services\AiVideo\AiMoviePipeline;
use App\Services\AiVideo\PythonAiWorkerClient;
use App\Services\Rendering\RenderQueueService;
use App\Services\Rendering\TimelineBuilder;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class GenerateBulkVideoVersionJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 3600;

    public int $tries = 2;

    public function __construct(private readonly int $videoVersionId)
    {
    }

    public function handle(
        AiMoviePipeline $moviePipeline,
        PythonAiWorkerClient $worker,
        AiProviderManager $providers,
        TimelineBuilder $timelineBuilder,
        RenderQueueService $renderQueue,
    ): void
    {
        $version = VideoVersion::query()
            ->with(['generation', 'videoProject.scenes'])
            ->find($this->videoVersionId);

        if (!$version || !$version->generation || !$version->videoProject) {
            return;
        }

        if (in_array($version->status, ['cancelled', 'completed', 'failed'], true)) {
            return;
        }

        $version->update([
            'status' => 'processing',
            'progress' => 15,
            'error_message' => null,
        ]);

        try {
            $this->runPlanningPipeline($version, $moviePipeline, $timelineBuilder);

            $this->generateSceneAssets($version, $worker, $providers);
            $this->generateVoiceTrack($version, $worker, $providers);
            $this->generateSubtitleTrack($version, $worker);

            $version->update([
                'status' => 'assets_ready',
                'progress' => 70,
                'timeline_json' => $timelineBuilder->buildForVersion($version->refresh())->toArray(),
            ]);

            $renderQueue->queueVersion($version->refresh(), $version->generation->render_provider);

            $this->refreshGenerationStatus($version->generation);
        } catch (Throwable $exception) {
            $version->update([
                'status' => 'failed',
                'progress' => 100,
                'error_message' => Str::limit($exception->getMessage(), 2000),
            ]);

            $this->refreshGenerationStatus($version->generation);

            throw $exception;
        }
    }

    private function runPlanningPipeline(VideoVersion $version, AiMoviePipeline $pipeline, TimelineBuilder $timelineBuilder): void
    {
        $version->loadMissing(['generation', 'videoProject.scenes']);
        if (!$version->generation || !$version->videoProject) {
            return;
        }

        if ($version->videoProject->scenes->isNotEmpty()) {
            return;
        }

        $preset = $version->style_payload ?? [];
        $settings = $version->generation->settings ?? [];

        $version->update(['progress' => 20]);
        $plan = $pipeline->plan($version->generation->prompt, [
            'language' => $version->generation->language,
            'duration_seconds' => (int) $version->duration_seconds,
            'aspect_ratio' => $version->aspect_ratio,
            'preset' => $preset,
            'scene_overrides' => (array) data_get($settings, 'scene_overrides', []),
            'editor_settings' => (array) data_get($settings, 'editor_settings', []),
        ]);

        $scriptText = (string) data_get($plan, 'script.full', $version->generation->prompt);
        $version->videoProject->update([
            'optimized_prompt' => $scriptText,
            'status' => VideoProjectStatus::Generating,
            'settings' => array_replace($version->videoProject->settings ?? [], [
                    'ai_movie_pipeline' => [
                        'version' => 'v2',
                        'steps' => $plan['pipeline'] ?? [],
                        'planner' => $plan['planner'] ?? [],
                        'script' => $plan['script'] ?? [],
                        'timeline' => $plan['timeline'] ?? [],
                    ],
            ]),
        ]);

        $this->completeOrCreatePrompt($version, AiPromptType::Script, $version->generation->prompt, [
            'script' => $plan['script'] ?? [],
            'pipeline' => 'AI Script Writer',
        ]);

        $version->update(['progress' => 30]);
        $transitionIds = Transition::query()->pluck('id', 'slug');

        foreach (($plan['scenes'] ?? []) as $scene) {
            $transitionSlug = (string) ($scene['transition'] ?? 'fade');
            $record = $version->videoProject->scenes()->create([
                'transition_id' => $transitionIds[$transitionSlug] ?? null,
                'sort_order' => (int) ($scene['sort_order'] ?? $scene['scene'] ?? 1),
                'title' => Str::limit((string) ($scene['title'] ?? 'Scene'), 120),
                'cinematic_description' => (string) ($scene['scene_prompt'] ?? $scene['visual'] ?? ''),
                'voice_over_text' => (string) ($scene['voice_over'] ?? ''),
                'subtitle_text' => (string) ($scene['subtitle'] ?? ''),
                'duration_seconds' => (float) ($scene['duration_seconds'] ?? 3),
                'camera_movement' => Str::limit((string) ($scene['camera_movement'] ?? $scene['camera'] ?? 'cinematic_zoom'), 80),
                'animation_style' => Str::limit((string) data_get($scene, 'motion.engine', 'ken_burns'), 80),
                'status' => VideoSceneStatus::Prompted,
                'metadata' => [
                    'pipeline_version' => 'ai_movie_pipeline_v2',
                    'scene_type' => $scene['type'] ?? null,
                    'shot_type' => $scene['shot_type'] ?? null,
                    'visual' => $scene['visual'] ?? null,
                    'b_roll_direction' => $scene['b_roll_direction'] ?? null,
                    'emotional_tone' => $scene['emotional_tone'] ?? null,
                    'pacing' => $scene['pacing'] ?? null,
                    'sound_effect' => $scene['sound_effect'] ?? null,
                    'transition_type' => $transitionSlug,
                    'shotstack_transition' => $transitionSlug,
                    'ai_prompt' => $scene['ai_prompt'] ?? null,
                    'scene_prompt' => $scene['scene_prompt'] ?? null,
                    'image_prompt' => $scene['image_prompt'] ?? null,
                    'video_prompt' => $scene['video_prompt'] ?? null,
                    'negative_prompt' => $scene['negative_prompt'] ?? null,
                    'asset_plan' => $scene['asset_plan'] ?? [],
                    'subtitle_style' => $scene['subtitle_style'] ?? $version->subtitle_style,
                    'subtitle_cues' => $scene['subtitle_cues'] ?? [],
                    'motion' => $scene['motion'] ?? [],
                ],
            ]);

            $this->completeOrCreatePrompt($version, AiPromptType::Scene, (string) ($scene['ai_prompt'] ?? $record->cinematic_description), [
                'scene_id' => $record->id,
                'scene' => $scene,
                'pipeline' => 'Scene Prompt Generator',
            ], $record->id);
        }

        $version->videoProject->update(['status' => VideoProjectStatus::Ready]);
        $version->update([
            'progress' => 40,
            'timeline_json' => $timelineBuilder->buildForVersion($version->refresh())->toArray(),
        ]);
    }

    private function generateSceneAssets(VideoVersion $version, PythonAiWorkerClient $worker, AiProviderManager $providers): void
    {
        $version->load('videoProject.scenes');

        foreach ($version->videoProject->scenes as $scene) {
            $scene->update(['status' => VideoSceneStatus::Generating]);

            $this->completeOrCreatePrompt($version, AiPromptType::Optimization, (string) data_get($scene->metadata, 'asset_plan.search_query', $scene->title), [
                'scene_id' => $scene->id,
                'asset_plan' => data_get($scene->metadata, 'asset_plan', []),
                'pipeline' => 'Asset Finder',
            ], $scene->id);

            $imagePrompt = (string) data_get($scene->metadata, 'image_prompt', $scene->cinematic_description);
            $videoPrompt = (string) data_get($scene->metadata, 'video_prompt', $scene->cinematic_description);

            $imagePromptLog = $this->prompt($version, $scene->id, AiPromptType::Image, $imagePrompt);
            try {
                $image = $this->generateImageAsset($version, $providers, $worker, $imagePrompt);

                $this->asset($scene->id, $imagePromptLog->id, AssetType::GeneratedImage, $image);
                $imagePromptLog->update(['status' => AiPromptStatus::Completed, 'response' => $image]);
            } catch (Throwable $exception) {
                $imagePromptLog->update([
                    'status' => AiPromptStatus::Failed,
                    'response' => ['error' => Str::limit($exception->getMessage(), 1000)],
                ]);
            }

            if ($this->shouldGenerateSceneVideoAsset($scene->metadata ?? [], $version)) {
                $videoPromptLog = $this->prompt($version, $scene->id, AiPromptType::Video, $videoPrompt);
                try {
                    $video = $this->generateVideoAsset($version, $providers, $worker, $videoPrompt);

                    $this->asset($scene->id, $videoPromptLog->id, AssetType::GeneratedVideo, $video);
                    $videoPromptLog->update(['status' => AiPromptStatus::Completed, 'response' => $video]);
                } catch (Throwable $exception) {
                    $videoPromptLog->update([
                        'status' => AiPromptStatus::Failed,
                        'response' => ['error' => Str::limit($exception->getMessage(), 1000)],
                    ]);
                }
            }

            $scene->update(['status' => VideoSceneStatus::Ready]);
        }
    }

    private function generateImageAsset(
        VideoVersion $version,
        AiProviderManager $providers,
        PythonAiWorkerClient $worker,
        string $prompt
    ): array {
        $provider = $this->assetProvider($version);
        if (!in_array($provider, ['local', 'comfyui'], true)) {
            try {
                $preferredProvider = in_array($provider, ['fal', 'replicate'], true)
                    ? $provider
                    : (string) config('bulk_ai_video.generation.cloud_image_bridge', 'fal');
                $response = $providers->generate('image', [
                    'prompt' => $prompt,
                    'model' => $this->imageModelFor($preferredProvider),
                    'aspect_ratio' => $version->aspect_ratio,
                    'size' => $this->openAiImageSize($version->aspect_ratio),
                ], $preferredProvider);

                $assetUrl = data_get($response->data, 'url') ?: $this->storeBase64Image(data_get($response->data, 'b64_json'));
                if ($assetUrl) {
                    return [
                        'provider' => $response->provider,
                        'status' => 'ready',
                        'asset_url' => $assetUrl,
                        'metadata' => [
                            'asset_type' => 'image',
                            'source' => 'ai_provider',
                        ],
                    ];
                }
            } catch (Throwable) {
                // Keep the pipeline moving with the local worker fallback.
            }
        }

        return $worker->generateImage([
            'prompt' => $prompt,
            'provider' => $provider === 'comfyui' ? 'comfyui' : 'local',
            'aspect_ratio' => $version->aspect_ratio,
        ]);
    }

    private function generateVideoAsset(
        VideoVersion $version,
        AiProviderManager $providers,
        PythonAiWorkerClient $worker,
        string $prompt
    ): array {
        $provider = $this->assetProvider($version);
        foreach ($this->videoProviderAttempts($provider) as $attempt) {
            try {
                $response = $providers->generate('video', [
                    'prompt' => $prompt,
                    'aspect_ratio' => $version->aspect_ratio,
                    'duration' => min(8, max(5, (int) ceil(((float) $version->duration_seconds) / 5))),
                    'model' => $attempt['model'],
                ], $attempt['provider']);

                if (data_get($response->data, 'url')) {
                    return [
                        'provider' => $response->provider,
                        'status' => data_get($response->data, 'status', 'ready'),
                        'asset_url' => data_get($response->data, 'url'),
                        'metadata' => [
                            'asset_type' => 'video',
                            'source' => 'ai_provider',
                            'job_id' => data_get($response->data, 'job_id'),
                            'raw_status' => data_get($response->data, 'status'),
                        ],
                    ];
                }
            } catch (Throwable) {
                // Keep the pipeline moving with the local worker fallback.
            }
        }

        return $worker->generateVideo([
            'prompt' => $prompt,
            'provider' => in_array($provider, ['comfyui', 'fal', 'replicate', 'kling', 'wan', 'ltx', 'minimax', 'veo'], true) ? $provider : 'local',
            'aspect_ratio' => $version->aspect_ratio,
        ]);
    }

    private function generateVoiceTrack(VideoVersion $version, PythonAiWorkerClient $worker, AiProviderManager $providers): void
    {
        if ($version->voice === 'none') {
            return;
        }

        $text = $this->narration($version);
        $track = VoiceTrack::create([
            'video_generation_id' => $version->video_generation_id,
            'video_version_id' => $version->id,
            'video_project_id' => $version->video_project_id,
            'provider' => $this->elevenLabsVoiceId($version) ? 'elevenlabs' : 'python-worker',
            'status' => 'processing',
            'language' => $version->generation->language,
            'voice' => $version->voice ?: 'female_south',
            'text' => $text,
        ]);

        try {
            $response = $this->generateVoiceAsset($version, $providers, $worker, $text);

            $track->update([
                'status' => $response['status'] ?? 'ready',
                'audio_path' => $response['audio_path'] ?? null,
                'duration_seconds' => $response['duration_seconds'] ?? null,
                'metadata' => $response,
            ]);
        } catch (Throwable $exception) {
            $track->update([
                'status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 1000),
            ]);
        }
    }

    private function generateVoiceAsset(
        VideoVersion $version,
        AiProviderManager $providers,
        PythonAiWorkerClient $worker,
        string $text
    ): array {
        $voiceId = $this->elevenLabsVoiceId($version);

        if ($voiceId) {
            try {
                $response = $providers->generate('voice', [
                    'text' => $text,
                    'prompt' => $text,
                    'voice_id' => $voiceId,
                    'model' => config('ai_providers.providers.elevenlabs.voice_model', 'eleven_multilingual_v2'),
                ], 'elevenlabs');

                $binary = data_get($response->data, 'binary');
                if (is_string($binary) && $binary !== '') {
                    $path = 'ai-video/voices/' . (string) Str::uuid() . '.mp3';
                    Storage::disk((string) config('ai_video_platform.storage.disk', 'public'))->put($path, $binary);

                    return [
                        'status' => 'ready',
                        'audio_path' => $path,
                        'duration_seconds' => null,
                        'provider' => 'elevenlabs',
                        'metadata' => [
                            'source' => 'elevenlabs',
                            'voice_id' => $voiceId,
                            'content_type' => data_get($response->data, 'content_type', 'audio/mpeg'),
                        ],
                    ];
                }
            } catch (Throwable) {
                // Keep the pipeline moving with the local worker fallback.
            }
        }

        return $worker->generateVoice([
            'text' => $text,
            'language' => $version->generation->language,
            'voice' => $version->voice ?: 'female_south',
        ]);
    }

    private function elevenLabsVoiceId(VideoVersion $version): ?string
    {
        $voice = (string) ($version->voice ?: 'female_south');
        $voiceId = config("ai_providers.providers.elevenlabs.voices.{$voice}")
            ?: config('ai_providers.providers.elevenlabs.default_voice_id');

        return is_string($voiceId) && trim($voiceId) !== '' ? trim($voiceId) : null;
    }

    private function voiceAudioPathForWorker(VideoVersion $version): ?string
    {
        $path = $version->voiceTracks()
            ->latest('id')
            ->value('audio_path');

        if (!is_string($path) || trim($path) === '') {
            return null;
        }

        if (preg_match('/^[A-Za-z]:\\\\/', $path) || str_starts_with($path, '/')) {
            return $path;
        }

        $publicPath = storage_path('app/public/' . ltrim($path, '/'));
        if (file_exists($publicPath)) {
            return $publicPath;
        }

        $storagePath = storage_path('app/' . ltrim($path, '/'));
        if (file_exists($storagePath)) {
            return $storagePath;
        }

        return $path;
    }

    private function generateSubtitleTrack(VideoVersion $version, PythonAiWorkerClient $worker): void
    {
        $text = $this->narration($version);
        $track = SubtitleTrack::create([
            'video_generation_id' => $version->video_generation_id,
            'video_version_id' => $version->id,
            'video_project_id' => $version->video_project_id,
            'provider' => 'python-worker',
            'status' => 'processing',
            'language' => $version->generation->language,
            'format' => 'srt',
            'content' => $text,
            'style' => [
                'name' => $version->subtitle_style,
                'pacing' => $version->pacing,
            ],
        ]);

        try {
            $response = $worker->generateSubtitles([
                'text' => $text,
                'language' => $version->generation->language,
                'style' => 'srt',
                'audio_path' => $this->voiceAudioPathForWorker($version),
                'use_whisper' => true,
            ]);

            $track->update([
                'status' => $response['status'] ?? 'ready',
                'subtitle_path' => $response['subtitle_path'] ?? null,
                'format' => $response['format'] ?? 'srt',
            ]);
        } catch (Throwable $exception) {
            $track->update([
                'status' => 'failed',
                'error_message' => Str::limit($exception->getMessage(), 1000),
            ]);
        }
    }

    private function prompt(VideoVersion $version, ?int $sceneId, AiPromptType $type, string $prompt): AiPrompt
    {
        return AiPrompt::create([
            'video_project_id' => $version->video_project_id,
            'video_scene_id' => $sceneId,
            'type' => $type,
            'provider' => $this->assetProvider($version),
            'model' => 'bulk-ai-video-worker',
            'prompt' => $prompt,
            'status' => AiPromptStatus::Running,
        ]);
    }

    private function completeOrCreatePrompt(
        VideoVersion $version,
        AiPromptType $type,
        string $prompt,
        array $response,
        ?int $sceneId = null,
    ): AiPrompt {
        $query = AiPrompt::query()
            ->where('video_project_id', $version->video_project_id)
            ->where('type', $type);

        if ($sceneId) {
            $query->where('video_scene_id', $sceneId);
        } else {
            $query->whereNull('video_scene_id');
        }

        $log = $query->oldest('id')->first() ?: AiPrompt::create([
            'video_project_id' => $version->video_project_id,
            'video_scene_id' => $sceneId,
            'type' => $type,
            'provider' => $this->assetProvider($version),
            'model' => (string) config('ai_video_platform.openai.model', 'gpt-5.5'),
            'prompt' => $prompt,
            'status' => AiPromptStatus::Queued,
        ]);

        $log->update([
            'prompt' => $prompt,
            'provider' => $this->assetProvider($version),
            'model' => (string) config('ai_video_platform.openai.model', 'gpt-5.5'),
            'response' => $response,
            'status' => AiPromptStatus::Completed,
        ]);

        return $log;
    }

    private function asset(int $sceneId, int $promptId, AssetType $type, array $response): ?SceneAsset
    {
        $path = $response['asset_url'] ?? null;
        if (!$path) {
            return null;
        }

        return SceneAsset::create([
            'video_scene_id' => $sceneId,
            'ai_prompt_id' => $promptId,
            'type' => $type,
            'provider' => $response['provider'] ?? 'python-worker',
            'path' => $path,
            'mime_type' => $response['mime_type'] ?? ($type === AssetType::GeneratedVideo ? 'video/mp4' : $this->imageMimeType((string) $path)),
            'status' => AssetStatus::Ready,
            'metadata' => $response['metadata'] ?? [],
        ]);
    }

    private function imageMimeType(string $path): string
    {
        return str_ends_with(strtolower(parse_url($path, PHP_URL_PATH) ?: $path), '.svg') ? 'image/svg+xml' : 'image/png';
    }

    private function shouldGenerateSceneVideoAsset(array $metadata, VideoVersion $version): bool
    {
        if ((bool) config('bulk_ai_video.generation.generate_scene_video_assets', false)) {
            return true;
        }

        $primary = (string) data_get($metadata, 'asset_plan.primary', '');

        return $this->assetProvider($version) !== 'local' && str_contains($primary, 'ai_video');
    }

    private function narration(VideoVersion $version): string
    {
        return $version->videoProject->scenes
            ->map(fn ($scene): string => trim((string) ($scene->voice_over_text ?: $scene->subtitle_text)))
            ->filter()
            ->implode(' ');
    }

    private function assetProvider(VideoVersion $version): string
    {
        $provider = (string) data_get($version->generation?->settings, 'provider', config('bulk_ai_video.generation.asset_provider', 'local'));

        return $provider === 'auto' ? 'local' : $provider;
    }

    private function openAiImageSize(string $aspectRatio): string
    {
        return match ($aspectRatio) {
            '16:9' => '1536x1024',
            '1:1' => '1024x1024',
            default => '1024x1536',
        };
    }

    private function imageModelFor(string $provider): ?string
    {
        $model = config("ai_providers.providers.{$provider}.image_model");

        return is_string($model) && trim($model) !== '' ? trim($model) : null;
    }

    private function videoProviderAttempts(string $provider): array
    {
        $attempts = [];
        $directModel = config("ai_providers.providers.{$provider}.video_model");

        if (in_array($provider, ['kling', 'runway', 'fal', 'replicate'], true) && is_string($directModel) && trim($directModel) !== '') {
            $attempts[] = [
                'provider' => $provider,
                'model' => trim($directModel),
            ];
        }

        $bridge = (string) config('bulk_ai_video.generation.cloud_video_bridge', 'fal');
        $bridgeModel = config("ai_providers.providers.{$bridge}.video_models.{$provider}");
        if (in_array($provider, ['kling', 'wan', 'ltx', 'minimax', 'veo'], true) && is_string($bridgeModel) && trim($bridgeModel) !== '') {
            $attempts[] = [
                'provider' => $bridge,
                'model' => trim($bridgeModel),
            ];
        }

        return collect($attempts)
            ->unique(fn (array $attempt): string => $attempt['provider'] . ':' . $attempt['model'])
            ->values()
            ->all();
    }

    private function storeBase64Image(mixed $payload): ?string
    {
        if (!is_string($payload) || trim($payload) === '') {
            return null;
        }

        $binary = base64_decode($payload, true);
        if ($binary === false || $binary === '') {
            return null;
        }

        $path = 'ai-video/assets/' . (string) Str::uuid() . '.png';
        Storage::disk((string) config('ai_video_platform.storage.disk', 'public'))->put($path, $binary);

        return $path;
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
