<?php

namespace Database\Seeders;

use App\Models\AiTemplate;
use App\Models\MusicTrack;
use App\Models\Product;
use App\Models\ProductAsset;
use App\Models\Template;
use App\Models\Transition;
use App\Models\User;
use App\Models\VoiceProfile;
use Illuminate\Database\Seeder;

class AiVideoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedTemplates();
        $this->seedBulkStyleTemplates();
        $this->seedTransitions();
        $this->seedVoices();
        $this->seedMusic();
    }

    private function seedBulkStyleTemplates(): void
    {
        $templates = [
            ['name' => 'TikTok Viral', 'slug' => 'bulk-tiktok-viral', 'platform' => 'tiktok', 'style' => 'viral_tiktok'],
            ['name' => 'Cinematic', 'slug' => 'bulk-cinematic', 'platform' => 'youtube', 'style' => 'cinematic'],
            ['name' => 'Anime', 'slug' => 'bulk-anime', 'platform' => 'shorts', 'style' => 'anime'],
            ['name' => 'Motivational Shorts', 'slug' => 'bulk-motivation', 'platform' => 'reels', 'style' => 'motivation'],
            ['name' => 'Modern Minimal', 'slug' => 'bulk-modern-minimal', 'platform' => 'linkedin', 'style' => 'modern_minimal'],
        ];

        foreach ($templates as $template) {
            Template::updateOrCreate(
                ['slug' => $template['slug']],
                [
                    'name' => $template['name'],
                    'type' => 'bulk_video_style',
                    'platform' => $template['platform'],
                    'style' => $template['style'],
                    'description' => 'Bulk AI video style preset for one-prompt multi-version generation.',
                    'settings' => ['version_count' => 5],
                    'is_active' => true,
                ]
            );
        }
    }

    private function seedProduct(?int $userId): void
    {
        $product = Product::updateOrCreate(
            ['sku' => 'AI-DEMO-RICE-001'],
            [
                'name' => 'Gạo Thuần Premium',
                'price' => 189000,
                'stock' => 120,
                'category' => 'Thực phẩm cao cấp',
                'brand' => 'Owl Select',
                'tags' => ['organic', 'premium', 'cinematic-ad'],
                'featured' => true,
                'synced_to_meta' => false,
                'status' => 'active',
                'product_form' => 'physical',
                'published_at' => now()->toDateString(),
                'seo_title' => 'Gạo Thuần Premium',
                'seo_description' => 'Sản phẩm mẫu cho pipeline dựng video quảng cáo AI cinematic.',
            ]
        );

        ProductAsset::updateOrCreate(
            [
                'product_id' => $product->id,
                'path' => 'demo/products/gao-thuan-premium-packshot.jpg',
            ],
            [
                'user_id' => $userId,
                'type' => 'product_image',
                'status' => 'ready',
                'provider' => 'internal',
                'thumbnail_path' => 'demo/products/gao-thuan-premium-thumb.jpg',
                'mime_type' => 'image/jpeg',
                'width' => 1600,
                'height' => 2000,
                'duration_seconds' => null,
                'is_primary' => true,
                'metadata' => [
                    'lighting' => 'softbox + rim light',
                    'usage' => 'hero product placement',
                ],
            ]
        );
    }

    private function seedTemplates(): void
    {
        $templates = [
            [
                'name' => 'Luxury TikTok Product Reveal',
                'slug' => 'luxury-tiktok-product-reveal',
                'language' => 'vi',
                'tone' => 'luxury',
                'style' => 'cinematic',
                'platform' => 'tiktok',
                'system_prompt' => 'Create a premium short-form ecommerce video with cinematic lighting, product depth, emotional hook, and high-retention pacing.',
                'script_prompt_template' => 'Write a 4-scene Vietnamese luxury TikTok ad for {{product_name}} with hook, reveal, transformation, and CTA.',
                'image_prompt_template' => 'Luxury cinematic product commercial, dark gallery lighting, realistic {{product_name}}, shallow depth of field, premium reflections.',
                'video_prompt_template' => 'Generate cinematic motion with dolly movement, parallax depth, realistic lighting, motion blur, product hero framing.',
                'voice_prompt_template' => 'Warm confident Vietnamese voice-over with premium commercial pacing.',
                'default_scene_structure' => [
                    'hook_opening',
                    'product_reveal',
                    'feature_transformation',
                    'cta_ending',
                ],
                'default_settings' => [
                    'aspect_ratio' => '9:16',
                    'duration_seconds' => 12,
                    'camera' => 'dolly_in',
                    'color_grade' => 'premium_contrast',
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Apple Style Product Showcase',
                'slug' => 'apple-style-product-showcase',
                'language' => 'en',
                'tone' => 'premium',
                'style' => 'product_showcase',
                'platform' => 'youtube',
                'system_prompt' => 'Design a refined product film with clean pacing, minimal copy, precise camera movement, and elegant lighting.',
                'script_prompt_template' => 'Write a concise 4-scene English premium product commercial for {{product_name}}.',
                'image_prompt_template' => 'High-end product commercial, clean studio, elegant reflections, realistic product texture, cinematic depth.',
                'video_prompt_template' => 'Slow orbit, subtle push-in, elegant light sweep, premium product macro details.',
                'voice_prompt_template' => 'Calm English commercial narration with understated premium tone.',
                'default_scene_structure' => [
                    'problem_hook',
                    'detail_macro',
                    'benefit_reveal',
                    'brand_close',
                ],
                'default_settings' => [
                    'aspect_ratio' => '16:9',
                    'duration_seconds' => 15,
                    'camera' => 'orbit',
                    'color_grade' => 'clean_luxury',
                ],
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            AiTemplate::updateOrCreate(['slug' => $template['slug']], $template);
        }
    }

    private function seedTransitions(): void
    {
        $transitions = [
            [
                'name' => 'Bloom Light Cut',
                'slug' => 'bloom-light-cut',
                'type' => 'cinematic',
                'duration_seconds' => 0.45,
                'ffmpeg_filter' => 'xfade=transition=fade:duration=0.45',
                'remotion_component' => 'BloomLightCut',
                'settings' => ['bloom' => 0.7, 'motion_blur' => true],
                'is_active' => true,
            ],
            [
                'name' => 'Parallax Push',
                'slug' => 'parallax-push',
                'type' => 'depth',
                'duration_seconds' => 0.65,
                'ffmpeg_filter' => 'xfade=transition=smoothleft:duration=0.65',
                'remotion_component' => 'ParallaxPush',
                'settings' => ['depth' => 0.55, 'ease' => 'expo.out'],
                'is_active' => true,
            ],
            [
                'name' => 'Whip Pan Glow',
                'slug' => 'whip-pan-glow',
                'type' => 'viral',
                'duration_seconds' => 0.35,
                'ffmpeg_filter' => 'xfade=transition=wiperight:duration=0.35',
                'remotion_component' => 'WhipPanGlow',
                'settings' => ['blur' => 18, 'glow' => 0.5],
                'is_active' => true,
            ],
        ];

        foreach ($transitions as $transition) {
            Transition::updateOrCreate(['slug' => $transition['slug']], $transition);
        }
    }

    private function seedVoices(): void
    {
        $voices = [
            [
                'provider' => 'elevenlabs',
                'provider_voice_id' => null,
                'name' => 'Giọng nữ miền Nam premium',
                'gender' => 'female',
                'language' => 'vi',
                'tone' => 'premium',
                'sample_path' => 'demo/voices/vi-female-premium.mp3',
                'settings' => ['stability' => 0.62, 'similarity_boost' => 0.78],
                'is_active' => true,
            ],
            [
                'provider' => 'elevenlabs',
                'provider_voice_id' => null,
                'name' => 'Giọng nam miền Bắc trầm ấm',
                'gender' => 'male',
                'language' => 'vi',
                'tone' => 'cinematic',
                'sample_path' => 'demo/voices/vi-male-cinematic.mp3',
                'settings' => ['stability' => 0.7, 'similarity_boost' => 0.72],
                'is_active' => true,
            ],
            [
                'provider' => 'elevenlabs',
                'provider_voice_id' => null,
                'name' => 'English luxury narrator',
                'gender' => 'neutral',
                'language' => 'en',
                'tone' => 'luxury',
                'sample_path' => 'demo/voices/en-luxury-narrator.mp3',
                'settings' => ['stability' => 0.66, 'similarity_boost' => 0.8],
                'is_active' => true,
            ],
        ];

        foreach ($voices as $voice) {
            VoiceProfile::updateOrCreate(
                ['provider' => $voice['provider'], 'name' => $voice['name']],
                $voice
            );
        }
    }

    private function seedMusic(): void
    {
        $tracks = [
            [
                'title' => 'Trending TikTok Pulse',
                'mood' => 'viral',
                'bpm' => 124,
                'duration_seconds' => 30,
                'file_path' => 'demo/music/trending-tiktok-pulse.mp3',
                'license_type' => 'internal',
                'default_volume' => 0.32,
                'metadata' => ['energy' => 'high', 'best_for' => '9:16 reels'],
                'is_active' => true,
            ],
            [
                'title' => 'Epic Cinematic Rise',
                'mood' => 'cinematic',
                'bpm' => 92,
                'duration_seconds' => 45,
                'file_path' => 'demo/music/epic-cinematic-rise.mp3',
                'license_type' => 'internal',
                'default_volume' => 0.28,
                'metadata' => ['energy' => 'medium', 'best_for' => 'premium reveal'],
                'is_active' => true,
            ],
            [
                'title' => 'Lo-fi Chill Commerce',
                'mood' => 'soft',
                'bpm' => 78,
                'duration_seconds' => 40,
                'file_path' => 'demo/music/lofi-chill-commerce.mp3',
                'license_type' => 'internal',
                'default_volume' => 0.24,
                'metadata' => ['energy' => 'low', 'best_for' => 'minimal showcase'],
                'is_active' => true,
            ],
        ];

        foreach ($tracks as $track) {
            MusicTrack::updateOrCreate(['title' => $track['title']], $track);
        }
    }
}
