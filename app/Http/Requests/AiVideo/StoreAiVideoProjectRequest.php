<?php

namespace App\Http\Requests\AiVideo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAiVideoProjectRequest extends FormRequest
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
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'prompt' => ['required', 'string', 'max:' . $maxPrompt],
            'template' => ['nullable', 'string', Rule::in([
                'tiktok_ai',
                'youtube_shorts',
                'instagram_reels',
                'faceless_story',
                'reddit_story',
                'quote_video',
                'product_showcase',
                'slideshow_ai',
                'anime_edit',
                'motivation',
            ])],
            'language' => ['nullable', 'string', Rule::in(['vi', 'en'])],
            'style' => ['nullable', 'string', 'max:80'],
            'aspect_ratio' => ['nullable', 'string', Rule::in(['9:16', '16:9', '1:1'])],
            'duration_seconds' => ['nullable', 'integer', 'min:5', 'max:' . $maxDuration],
            'voice' => ['nullable', 'string', 'max:80'],
            'music' => ['nullable', 'string', 'max:80'],
            'provider' => ['nullable', 'string', Rule::in(['auto', 'comfyui', 'fal', 'replicate', 'runway', 'shotstack'])],
            'render_immediately' => ['nullable', 'boolean'],
        ];
    }
}

