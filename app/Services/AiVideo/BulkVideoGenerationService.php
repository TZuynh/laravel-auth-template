<?php

namespace App\Services\AiVideo;

use App\DTOs\AiVideo\BulkVideoGenerationRequestData;
use App\Enums\AiPromptStatus;
use App\Enums\AiPromptType;
use App\Enums\VideoProjectStatus;
use App\Jobs\AiVideo\GenerateBulkVideoVersionJob;
use App\Models\AiPrompt;
use App\Models\User;
use App\Models\VideoGeneration;
use App\Models\VideoProject;
use App\Models\VideoVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BulkVideoGenerationService
{
    public function __construct(
        private readonly StylePresetEngine $styles,
    ) {
    }

    public function create(User $user, BulkVideoGenerationRequestData $data): VideoGeneration
    {
        $generation = DB::transaction(function () use ($user, $data): VideoGeneration {
            $preset = $this->styles->find($data->styleSlug) ?: $this->styles->presets()[0];

            $generation = VideoGeneration::create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'title' => $this->title($data->prompt, $preset),
                'prompt' => $data->prompt,
                'language' => $data->language,
                'aspect_ratio' => $data->aspectRatio,
                'duration_seconds' => $data->durationSeconds,
                'provider' => $data->provider,
                'render_provider' => $data->renderProvider,
                'status' => $data->renderImmediately ? 'queued' : 'ready',
                'requested_versions' => 1,
                'settings' => $data->toSettings(),
            ]);

            $this->createVersion($generation, $user, $data, $preset);

            return $generation->load(['versions.videoProject.scenes.transition']);
        });

        if ($data->renderImmediately) {
            $generation->versions->each(function (VideoVersion $version): void {
                GenerateBulkVideoVersionJob::dispatch($version->id)
                    ->onQueue((string) config('bulk_ai_video.generation.queue', 'ai-text'));
            });
        }

        return $generation;
    }

    private function createVersion(VideoGeneration $generation, User $user, BulkVideoGenerationRequestData $data, array $preset): VideoVersion
    {
        $preset = $this->applyEditorSettings($preset, $data->editorSettings);
        $aspectRatio = $data->aspectRatio ?: ($preset['aspect_ratio'] ?? '9:16');

        $project = VideoProject::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'title' => $preset['name'] . ' - ' . Str::limit($this->subject($data->prompt), 48),
            'language' => $data->language,
            'tone' => $preset['slug'],
            'style' => $preset['style'],
            'aspect_ratio' => $aspectRatio,
            'duration_seconds' => $data->durationSeconds,
            'ai_model' => $data->provider,
            'prompt' => $data->prompt,
            'optimized_prompt' => null,
            'settings' => [
                'bulk_generation_id' => $generation->id,
                'style_preset' => $preset,
                'voice' => $preset['voice'],
                'music' => $preset['music'],
                'subtitle_style' => $preset['subtitle_style'],
                'editor_settings' => $data->editorSettings,
                'scene_overrides' => $data->sceneOverrides,
                'pipeline' => 'ai_movie_generation',
                'render_provider' => $data->renderProvider,
            ],
            'status' => $data->renderImmediately ? VideoProjectStatus::Generating : VideoProjectStatus::Draft,
        ]);

        $version = VideoVersion::create([
            'uuid' => (string) Str::uuid(),
            'video_generation_id' => $generation->id,
            'video_project_id' => $project->id,
            'title' => $project->title,
            'style_slug' => $preset['slug'],
            'style_name' => $preset['name'],
            'platform' => $preset['platform'],
            'aspect_ratio' => $aspectRatio,
            'duration_seconds' => $data->durationSeconds,
            'voice' => $preset['voice'],
            'music' => $preset['music'],
            'subtitle_style' => $preset['subtitle_style'],
            'pacing' => $preset['pacing'],
            'visual_direction' => $preset['visual_direction'],
            'style_payload' => $preset,
            'status' => $data->renderImmediately ? 'queued' : 'ready',
            'progress' => $data->renderImmediately ? 5 : 20,
        ]);

        AiPrompt::create([
            'video_project_id' => $project->id,
            'type' => AiPromptType::Script,
            'provider' => $data->provider,
            'model' => (string) config('ai_video_platform.openai.model', 'gpt-5.5'),
            'prompt' => $this->scriptWriterPrompt($data->prompt, $data->language, $preset),
            'response' => [
                'pipeline' => 'queued',
                'preset' => $preset['slug'],
            ],
            'status' => AiPromptStatus::Queued,
        ]);

        return $version;
    }

    private function scriptWriterPrompt(string $prompt, string $language, array $preset): string
    {
        return implode(PHP_EOL, [
            'Generate cinematic scene-by-scene timeline.',
            'Each scene must include: shot type, camera movement, subtitle, B-roll direction, transition, sound effect, pacing, emotional tone, duration.',
            'Language: ' . $language,
            'Visual style: ' . ($preset['visual_direction'] ?? 'cinematic social video'),
            'Source prompt: ' . $prompt,
        ]);
    }

    private function applyEditorSettings(array $preset, array $editorSettings): array
    {
        $transition = trim((string) data_get($editorSettings, 'transition', ''));
        $subtitleStyle = trim((string) data_get($editorSettings, 'subtitle_style', ''));
        $music = trim((string) data_get($editorSettings, 'music', ''));
        $voice = trim((string) data_get($editorSettings, 'voice', ''));
        $pacing = trim((string) data_get($editorSettings, 'pacing', ''));
        $visualDirection = trim((string) data_get($editorSettings, 'visual_direction', ''));

        if ($transition !== '') {
            $preset['transitions'] = array_fill(0, 8, $transition);
        }

        if ($subtitleStyle !== '') {
            $preset['subtitle_style'] = Str::limit($subtitleStyle, 120);
        }

        if ($music !== '') {
            $preset['music'] = Str::limit($music, 120);
        }

        if ($voice !== '') {
            $preset['voice'] = Str::limit($voice, 80);
        }

        if ($pacing !== '') {
            $preset['pacing'] = Str::limit($pacing, 120);
        }

        if ($visualDirection !== '') {
            $preset['visual_direction'] = Str::limit($visualDirection, 320);
        }

        return $preset;
    }

    private function title(string $prompt, array $preset): string
    {
        return $preset['name'] . ' Video - ' . Str::limit(Str::headline($prompt), 64);
    }

    private function subject(string $prompt): string
    {
        return Str::limit(Str::headline($prompt), 72);
    }
}
