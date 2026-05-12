<?php

namespace App\Services\AiVideo;

class StylePresetEngine
{
    public function presets(): array
    {
        return [
            [
                'slug' => 'ai_studio',
                'name' => 'AI Studio',
                'platform' => 'shorts',
                'style' => 'studio_auto',
                'aspect_ratio' => '9:16',
                'voice' => 'female_south',
                'music' => 'Neutral Ambient Pulse',
                'subtitle_style' => 'clean bold captions',
                'pacing' => 'balanced 2-3 second cuts',
                'visual_direction' => 'clean premium visuals, smooth motion, product-first framing, gentle contrast, natural cinematic light',
                'transitions' => ['fade', 'zoom', 'wipeLeft', 'slideUp'],
                'effects' => ['zoomIn', 'slideRight'],
            ],
            [
                'slug' => 'tiktok_viral',
                'name' => 'TikTok Viral',
                'platform' => 'tiktok',
                'style' => 'viral_tiktok',
                'aspect_ratio' => '9:16',
                'voice' => 'female_south',
                'music' => 'Trending TikTok Pulse',
                'subtitle_style' => 'animated bold captions',
                'pacing' => 'fast cuts every 1.5-2 seconds',
                'visual_direction' => 'high contrast, snap zooms, handheld push-ins, kinetic stickers, bright social energy',
                'transitions' => ['whipRight', 'zoom', 'wipeLeft', 'slideUp'],
                'effects' => ['zoomIn', 'slideRight'],
            ],
            [
                'slug' => 'cinematic',
                'name' => 'Cinematic',
                'platform' => 'youtube',
                'style' => 'cinematic',
                'aspect_ratio' => '16:9',
                'voice' => 'ai_en',
                'music' => 'Epic Cinematic Rise',
                'subtitle_style' => 'small lower-third cinema subtitles',
                'pacing' => 'slow emotional reveal with 3-4 second shots',
                'visual_direction' => 'movie lighting, volumetric atmosphere, slow motion, dolly moves, dramatic transitions',
                'transitions' => ['fade', 'wipeLeft', 'fade', 'slideLeft'],
                'effects' => ['zoomInSlow', 'slideLeft'],
            ],
            [
                'slug' => 'anime',
                'name' => 'Anime',
                'platform' => 'shorts',
                'style' => 'anime',
                'aspect_ratio' => '9:16',
                'voice' => 'ai_en',
                'music' => 'Anime Energy Loop',
                'subtitle_style' => 'outlined anime captions with glow',
                'pacing' => 'dynamic action beats with speed ramps',
                'visual_direction' => 'anime color grading, cel-shaded light, impact frames, glow effects, dramatic motion lines',
                'transitions' => ['wipeRight', 'zoom', 'wipeLeft', 'fade'],
                'effects' => ['zoomIn', 'slideUp'],
            ],
            [
                'slug' => 'motivation',
                'name' => 'Motivational Shorts',
                'platform' => 'reels',
                'style' => 'motivation',
                'aspect_ratio' => '9:16',
                'voice' => 'male_north',
                'music' => 'Epic Cinematic Rise',
                'subtitle_style' => 'large quote captions with emphasis words',
                'pacing' => 'emotional build with bold quote hits',
                'visual_direction' => 'cinematic quotes, sunrise contrast, disciplined routines, epic music, strong typography',
                'transitions' => ['fade', 'slideUp', 'wipeLeft', 'fade'],
                'effects' => ['zoomIn', 'slideRight'],
            ],
            [
                'slug' => 'modern_minimal',
                'name' => 'Modern Minimal',
                'platform' => 'linkedin',
                'style' => 'modern_minimal',
                'aspect_ratio' => '1:1',
                'voice' => 'female_south',
                'music' => 'Lo-fi Chill Commerce',
                'subtitle_style' => 'clean typography with soft highlight bar',
                'pacing' => 'smooth measured pacing with soft transitions',
                'visual_direction' => 'clean typography, smooth transitions, soft colors, generous negative space, modern editorial motion',
                'transitions' => ['fade', 'slideLeft', 'fade', 'slideRight'],
                'effects' => ['slideRight', 'zoomIn'],
            ],
        ];
    }

    public function find(string $slug): ?array
    {
        return collect($this->presets())->firstWhere('slug', $slug);
    }
}
