<?php

namespace App\Repositories\Eloquent;

use App\Models\AiTemplate;
use App\Models\AiImageGeneration;
use App\Models\Export;
use App\Models\MusicTrack;
use App\Models\Product;
use App\Models\RenderJob;
use App\Models\Transition;
use App\Models\VideoProject;
use App\Models\VoiceProfile;
use App\Repositories\Contracts\MarketingRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;

class MarketingRepository implements MarketingRepositoryInterface
{
    private const HIDDEN_MARKETING_PRODUCT_SKUS = ['AI-DEMO-RICE-001'];

    public function __construct(private readonly ProductRepositoryInterface $products)
    {
    }

    public function aiVideoStudioData(): array
    {
        $locale = in_array(app()->getLocale(), ['vi', 'en'], true) ? app()->getLocale() : 'vi';
        $products = $this->marketingProductQuery()
            ->select(['id', 'name', 'sku', 'image', 'price', 'category', 'brand', 'seo_title', 'seo_description'])
            ->latest('id')
            ->limit(30)
            ->get()
            ->map(function (Product $product) use ($locale): array {
                $display = $this->products->transformProductForDisplay($product, $locale);
                $image = trim((string) $product->image);

                return [
                    'id' => $product->id,
                    'name' => $display['name'] ?? $product->name,
                    'sku' => $product->sku,
                    'category' => $display['category'] ?? $product->category,
                    'brand' => $display['brand'] ?? $product->brand,
                    'image' => $this->imageUrl($image),
                    'brief' => $product->seo_description ?: $product->seo_title ?: $product->category,
                ];
            })
            ->values()
            ->all();

        if ($products === []) {
            $products = $this->fallbackProducts();
        }

        return [
            'products' => $products,
            'videoPresets' => [
                [
                    'value' => 'runway_generate',
                    'label' => __('messages.marketing.preset_runway_generate'),
                    'duration' => 8,
                    'fps' => 24,
                    'width' => 1280,
                    'height' => 720,
                ],
                [
                    'value' => 'product_ad_8s',
                    'label' => __('messages.marketing.preset_product_ad_8s'),
                    'duration' => 8,
                    'fps' => 24,
                    'width' => 1280,
                    'height' => 720,
                ],
                [
                    'value' => 'social_storyboard',
                    'label' => __('messages.marketing.preset_social_storyboard'),
                    'duration' => 9,
                    'fps' => 30,
                    'width' => 720,
                    'height' => 1280,
                ],
            ],
            'generationModes' => [
                ['value' => 'text_to_video', 'label' => __('messages.marketing.mode_text_to_video')],
                ['value' => 'image_to_video', 'label' => __('messages.marketing.mode_image_to_video')],
                ['value' => 'product_to_video', 'label' => __('messages.marketing.mode_product_to_video')],
            ],
            'aiModels' => [
                ['value' => 'gen4.5', 'label' => 'Runway Gen-4.5'],
                ['value' => 'gen4_turbo', 'label' => 'Runway Gen-4 Turbo'],
                ['value' => 'cinematic_product', 'label' => __('messages.marketing.model_cinematic_product')],
                ['value' => 'social_fast', 'label' => __('messages.marketing.model_social_fast')],
            ],
            'durations' => [
                ['value' => 5, 'label' => '5s'],
                ['value' => 8, 'label' => '8s'],
                ['value' => 10, 'label' => '10s'],
            ],
            'motionLevels' => [
                ['value' => 'low', 'label' => __('messages.marketing.motion_low')],
                ['value' => 'medium', 'label' => __('messages.marketing.motion_medium')],
                ['value' => 'high', 'label' => __('messages.marketing.motion_high')],
            ],
            'cameraMoves' => [
                ['value' => 'push_in', 'label' => __('messages.marketing.camera_push_in')],
                ['value' => 'orbit', 'label' => __('messages.marketing.camera_orbit')],
                ['value' => 'handheld', 'label' => __('messages.marketing.camera_handheld')],
                ['value' => 'dolly_left', 'label' => __('messages.marketing.camera_dolly_left')],
                ['value' => 'static', 'label' => __('messages.marketing.camera_static')],
            ],
            'characters' => [
                ['value' => 'none', 'label' => __('messages.marketing.character_none')],
                ['value' => 'presenter', 'label' => __('messages.marketing.character_presenter')],
                ['value' => 'customer', 'label' => __('messages.marketing.character_customer')],
                ['value' => 'creator', 'label' => __('messages.marketing.character_creator')],
            ],
            'genders' => [
                ['value' => 'female', 'label' => __('messages.marketing.gender_female')],
                ['value' => 'male', 'label' => __('messages.marketing.gender_male')],
                ['value' => 'neutral', 'label' => __('messages.marketing.gender_neutral')],
            ],
            'visualStyles' => [
                ['value' => 'cinematic', 'label' => __('messages.marketing.visual_cinematic')],
                ['value' => 'minimalist', 'label' => __('messages.marketing.visual_minimalist')],
                ['value' => 'film35', 'label' => __('messages.marketing.visual_film35')],
                ['value' => 'cyberpunk', 'label' => __('messages.marketing.visual_cyberpunk')],
            ],
            'frames' => [
                ['value' => '16:9', 'label' => '16:9 (Youtube)'],
                ['value' => '9:16', 'label' => '9:16 (TikTok/Reels)'],
                ['value' => '1:1', 'label' => '1:1 (Feed)'],
            ],
            'music' => [
                ['value' => 'tiktok', 'label' => __('messages.marketing.music_tiktok')],
                ['value' => 'epic', 'label' => __('messages.marketing.music_epic')],
                ['value' => 'lofi', 'label' => __('messages.marketing.music_lofi')],
                ['value' => 'funny', 'label' => __('messages.marketing.music_funny')],
                ['value' => 'none', 'label' => __('messages.marketing.music_none')],
            ],
            'voices' => [
                ['value' => 'female_south', 'label' => __('messages.marketing.voice_female_south')],
                ['value' => 'male_north', 'label' => __('messages.marketing.voice_male_north')],
                ['value' => 'ai_en', 'label' => __('messages.marketing.voice_ai_en')],
                ['value' => 'none', 'label' => __('messages.marketing.voice_none')],
            ],
            'filters' => [
                'markets' => [__('messages.marketing.filter_vietnam'), __('messages.marketing.filter_global')],
                'ranges' => [__('messages.marketing.filter_30_days'), __('messages.marketing.filter_7_days'), __('messages.marketing.filter_this_quarter')],
                'audiences' => [__('messages.marketing.filter_mass'), __('messages.marketing.filter_retarget'), __('messages.marketing.filter_new_customers')],
                'channels' => [__('messages.marketing.filter_all'), 'TikTok', 'Facebook', 'Youtube Shorts'],
                'industries' => [__('messages.marketing.filter_technology'), __('messages.marketing.filter_furniture'), __('messages.marketing.filter_retail')],
                'modes' => [__('messages.marketing.filter_expert'), __('messages.marketing.filter_creator'), __('messages.marketing.filter_performance')],
            ],
        ];
    }

    public function directorDashboardData(): array
    {
        return [
            'metrics' => [
                ['label' => 'Dự án video', 'value' => $this->safeCount(VideoProject::class), 'hint' => 'AI briefs đã tạo'],
                ['label' => 'Đang render', 'value' => $this->safeCount(RenderJob::class, ['status' => 'rendering']), 'hint' => 'Queue FFmpeg/AI'],
                ['label' => 'Template', 'value' => $this->safeCount(AiTemplate::class, ['is_active' => true]), 'hint' => 'Phong cách khả dụng'],
                ['label' => 'Xuất MP4', 'value' => $this->safeCount(Export::class, ['status' => 'ready']), 'hint' => 'File sẵn sàng tải'],
            ],
            'pipeline' => [
                ['label' => 'AI Script', 'status' => 'online', 'progress' => 88],
                ['label' => 'Visual Prompt', 'status' => 'online', 'progress' => 74],
                ['label' => 'Voice + Music', 'status' => 'ready', 'progress' => 61],
                ['label' => 'FFmpeg Render', 'status' => 'queue', 'progress' => 42],
            ],
            'recentProjects' => rescue(fn () => VideoProject::query()
                ->with('product')
                ->latest('id')
                ->limit(5)
                ->get()
                ->map(fn (VideoProject $project): array => [
                    'title' => $project->title,
                    'product' => $project->product?->name ?? 'No product',
                    'style' => $project->style,
                    'aspect' => $project->aspect_ratio?->value ?? (string) $project->aspect_ratio,
                    'status' => $project->status?->value ?? (string) $project->status,
                ])
                ->all(), [], false),
        ];
    }

    public function sceneEditorData(): array
    {
        $latestProject = rescue(fn () => VideoProject::query()
            ->with(['scenes.transition', 'product'])
            ->latest('id')
            ->first(), null, false);

        $scenes = $latestProject?->scenes?->map(fn ($scene): array => [
            'order' => $scene->sort_order,
            'title' => $scene->title,
            'description' => $scene->cinematic_description ?: 'Cinematic depth, product detail, premium light sweep.',
            'duration' => $scene->duration_seconds,
            'camera' => $scene->camera_movement,
            'transition' => $scene->transition?->name ?? 'Bloom Light Cut',
            'subtitle' => $scene->subtitle_text ?: $scene->voice_over_text,
            'status' => $scene->status?->value ?? (string) $scene->status,
        ])->all() ?? [];

        if ($scenes === []) {
            $scenes = [
                ['order' => 1, 'title' => 'Hook opening', 'description' => 'Dolly-in qua ánh sáng tối, mở bằng vấn đề khách hàng.', 'duration' => '2.000', 'camera' => 'dolly_in', 'transition' => 'Bloom Light Cut', 'subtitle' => 'Bạn đang bỏ lỡ khoảnh khắc khiến khách dừng cuộn.', 'status' => 'draft'],
                ['order' => 2, 'title' => 'Product reveal', 'description' => 'Sản phẩm xuất hiện trên nền phản chiếu, có parallax depth.', 'duration' => '3.000', 'camera' => 'orbit', 'transition' => 'Parallax Push', 'subtitle' => 'Một sản phẩm, dựng như quảng cáo điện ảnh.', 'status' => 'draft'],
                ['order' => 3, 'title' => 'Feature transformation', 'description' => 'Macro detail, kinetic typography và glow highlight lợi ích.', 'duration' => '3.000', 'camera' => 'cinematic_zoom', 'transition' => 'Whip Pan Glow', 'subtitle' => 'Từng chi tiết được biến thành lý do mua hàng.', 'status' => 'draft'],
                ['order' => 4, 'title' => 'CTA ending', 'description' => 'Brand lockup, CTA rõ, nhạc nâng cao ở nhịp cuối.', 'duration' => '2.000', 'camera' => 'dolly_out', 'transition' => 'Bloom Light Cut', 'subtitle' => 'Tạo chiến dịch mới ngay hôm nay.', 'status' => 'draft'],
            ];
        }

        return [
            'project' => [
                'id' => $latestProject?->id,
                'title' => $latestProject?->title ?? 'Luxury Product Reel',
                'product' => $latestProject?->product?->name ?? __('messages.marketing.sample_product_name'),
                'status' => $latestProject?->status?->value ?? 'draft',
            ],
            'scenes' => $scenes,
        ];
    }

    public function aiImageStudioData(): array
    {
        return [
            'products' => $this->productOptions(40),
            'styles' => [
                ['value' => 'cinematic', 'label' => 'Cinematic commerce'],
                ['value' => 'luxury', 'label' => 'Luxury editorial'],
                ['value' => 'minimalist', 'label' => 'Apple clean studio'],
                ['value' => 'cyberpunk', 'label' => 'Neon viral'],
            ],
            'aspects' => [
                ['value' => '9:16', 'label' => '9:16 TikTok/Reels'],
                ['value' => '16:9', 'label' => '16:9 YouTube'],
                ['value' => '1:1', 'label' => '1:1 Instagram'],
                ['value' => '4:5', 'label' => '4:5 Feed'],
            ],
            'providers' => [
                ['value' => 'openai', 'label' => 'OpenAI Image'],
                ['value' => 'local-cinematic', 'label' => 'Local cinematic fallback'],
            ],
            'models' => [
                ['value' => config('ai_providers.providers.openai.image_model', 'gpt-image-1'), 'label' => 'OpenAI image model'],
                ['value' => 'dall-e-3', 'label' => 'DALL-E 3'],
            ],
            'generations' => rescue(fn () => AiImageGeneration::query()
                ->with('product')
                ->latest('id')
                ->limit(18)
                ->get()
                ->map(fn (AiImageGeneration $generation): array => [
                    'id' => $generation->id,
                    'project' => $generation->product?->name ?? 'Marketing visual',
                    'style' => $generation->style,
                    'aspect' => $generation->aspect_ratio,
                    'provider' => $generation->provider,
                    'status' => $generation->status,
                    'prompt' => $generation->prompt,
                    'image' => $generation->imageUrl(),
                    'path' => $generation->image_path,
                    'size' => ($generation->width ?: '-') . 'x' . ($generation->height ?: '-'),
                    'created' => $generation->created_at?->format('d/m/Y H:i') ?? '-',
                    'source' => $generation->metadata['source'] ?? null,
                ])
                ->all(), [], false),
        ];
    }

    public function renderHistoryData(): array
    {
        $jobs = rescue(fn () => RenderJob::query()
            ->with('videoProject')
            ->latest('id')
            ->limit(20)
            ->get()
            ->map(fn (RenderJob $job): array => [
                'id' => $job->id,
                'uuid' => $job->uuid,
                'project' => $job->videoProject?->title ?? 'Untitled project',
                'type' => $job->type?->value ?? (string) $job->type,
                'provider' => $job->provider ?? 'internal',
                'status' => $job->status?->value ?? (string) $job->status,
                'progress' => $job->progress,
                'step' => $job->current_step ?? 'Waiting',
                'error' => $job->error_message,
                'started' => $job->started_at?->format('d/m/Y H:i') ?? '-',
                'created' => $job->created_at?->format('d/m/Y H:i') ?? '-',
            ])
            ->all(), [], false);

        return [
            'jobs' => $jobs,
            'queueStats' => [
                ['label' => 'Đang chờ', 'value' => $this->safeCount(RenderJob::class, ['status' => 'queued'])],
                ['label' => 'FFmpeg', 'value' => $this->safeCount(RenderJob::class, ['type' => 'render_final_video'])],
                ['label' => 'Lỗi / retry', 'value' => $this->safeCount(RenderJob::class, ['status' => 'failed'])],
            ],
        ];
    }

    public function exportManagerData(): array
    {
        return [
            'exports' => rescue(fn () => Export::query()
                ->with('videoProject')
                ->latest('id')
                ->limit(20)
                ->get()
                ->map(fn (Export $export): array => [
                    'id' => $export->id,
                    'uuid' => $export->uuid,
                    'project' => $export->videoProject?->title ?? 'Untitled project',
                    'format' => strtoupper($export->format),
                    'aspect' => $export->aspect_ratio?->value ?? (string) $export->aspect_ratio,
                    'resolution' => $export->resolution_width . 'x' . $export->resolution_height,
                    'duration' => $export->duration_seconds,
                    'status' => $export->status?->value ?? (string) $export->status,
                    'path' => $export->file_path,
                ])
                ->all(), [], false),
            'formats' => [
                ['label' => 'TikTok / Reels', 'aspect' => '9:16', 'resolution' => '1080x1920'],
                ['label' => 'YouTube', 'aspect' => '16:9', 'resolution' => '1920x1080'],
                ['label' => 'Instagram Feed', 'aspect' => '1:1', 'resolution' => '1080x1080'],
            ],
        ];
    }

    public function templateManagerData(): array
    {
        return [
            'templates' => rescue(fn () => AiTemplate::query()
                ->latest('id')
                ->get()
                ->map(fn (AiTemplate $template): array => [
                    'name' => $template->name,
                    'slug' => $template->slug,
                    'language' => $template->language,
                    'tone' => $template->tone,
                    'style' => $template->style,
                    'platform' => $template->platform,
                    'active' => $template->is_active,
                ])
                ->all(), [], false),
            'voices' => rescue(fn () => VoiceProfile::query()
                ->where('is_active', true)
                ->orderBy('language')
                ->get(['name', 'language', 'gender', 'tone'])
                ->toArray(), [], false),
            'music' => rescue(fn () => MusicTrack::query()
                ->where('is_active', true)
                ->orderBy('mood')
                ->get(['title', 'mood', 'bpm', 'default_volume'])
                ->toArray(), [], false),
            'transitions' => rescue(fn () => Transition::query()
                ->where('is_active', true)
                ->orderBy('type')
                ->get(['name', 'type', 'duration_seconds', 'remotion_component'])
                ->toArray(), [], false),
        ];
    }

    private function safeCount(string $modelClass, array $where = []): int
    {
        return rescue(function () use ($modelClass, $where): int {
            $query = $modelClass::query();

            foreach ($where as $column => $value) {
                $query->where($column, $value);
            }

            return $query->count();
        }, 0, false);
    }

    private function imageUrl(string $image): ?string
    {
        if ($image === '') {
            return null;
        }

        if (str_starts_with($image, 'http://') || str_starts_with($image, 'https://')) {
            return $image;
        }

        return asset('storage/' . ltrim($image, '/'));
    }

    private function productOptions(int $limit): array
    {
        return $this->marketingProductQuery()
            ->select(['id', 'name', 'sku', 'image', 'price', 'category', 'brand', 'seo_title', 'seo_description'])
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(function (Product $product): array {
                $display = $this->products->transformProductForDisplay($product, app()->getLocale());

                return [
                    'id' => $product->id,
                    'name' => $display['name'] ?? $product->name,
                    'sku' => $product->sku,
                    'category' => $display['category'] ?? $product->category,
                    'brand' => $display['brand'] ?? $product->brand,
                    'image' => $this->imageUrl((string) $product->image),
                    'brief' => $product->seo_description ?: $product->seo_title ?: $product->category,
                ];
            })
            ->values()
            ->all();
    }

    private function fallbackProducts(): array
    {
        return [
            [
                'id' => 0,
                'name' => __('messages.marketing.sample_product_name'),
                'sku' => 'DEMO-AI-001',
                'category' => __('messages.marketing.sample_product_category'),
                'brand' => 'Owl Studio',
                'image' => null,
                'brief' => __('messages.marketing.sample_product_brief'),
            ],
        ];
    }

    private function marketingProductQuery()
    {
        return Product::query()
            ->whereNotIn('sku', self::HIDDEN_MARKETING_PRODUCT_SKUS)
            ->where(function ($query): void {
                $query
                    ->whereNull('name')
                    ->orWhere('name', 'not like', '%Gạo Thuần%');
            });
    }
}
