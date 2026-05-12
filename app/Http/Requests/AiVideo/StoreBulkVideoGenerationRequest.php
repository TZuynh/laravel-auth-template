<?php

namespace App\Http\Requests\AiVideo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBulkVideoGenerationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $maxPrompt = (int) config('ai_video_platform.limits.prompt_max_chars', 5000);
        $maxDuration = (int) config('ai_video_platform.limits.max_duration_seconds', 90);

        return [
            'prompt' => ['required', 'string', 'min:6', 'max:' . $maxPrompt],
            'style_slug' => ['nullable', 'string', Rule::in([
                'ai_studio',
                'tiktok_viral',
                'cinematic',
                'anime',
                'motivation',
                'modern_minimal',
            ])],
            'language' => ['nullable', 'string', Rule::in(['vi', 'en'])],
            'aspect_ratio' => ['nullable', 'string', Rule::in(['9:16', '16:9', '1:1'])],
            'duration_seconds' => ['nullable', 'integer', 'min:10', 'max:' . $maxDuration],
            'provider' => ['nullable', 'string', Rule::in(['auto', 'local', 'comfyui', 'fal', 'replicate', 'runway', 'shotstack', 'kling', 'wan', 'ltx', 'minimax', 'veo'])],
            'render_provider' => ['nullable', 'string', Rule::in(['ffmpeg', 'shotstack', 'python', 'remotion'])],
            'render_immediately' => ['nullable', 'boolean'],
            'scene_overrides' => ['nullable', 'json'],
            'editor_settings' => ['nullable', 'json'],
        ];
    }
}
