<?php

namespace App\Services\AiVideo;

use App\DTOs\AiVideo\VideoGenerationRequestData;
use App\Enums\VideoProjectStatus;
use App\Enums\VideoSceneStatus;
use App\Models\Product;
use App\Models\Transition;
use App\Models\User;
use App\Models\VideoProject;
use App\Repositories\Contracts\AiVideoProjectRepositoryInterface;
use App\Services\Marketing\SceneGenerationService;
use App\Services\Rendering\RenderJobDispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AiVideoProjectOrchestrator
{
    public function __construct(
        private readonly SceneGenerationService $sceneGeneration,
        private readonly RenderJobDispatcher $renderJobs,
        private readonly AiVideoProjectRepositoryInterface $projects,
    ) {
    }

    public function create(User $user, VideoGenerationRequestData $data): VideoProject
    {
        return DB::transaction(function () use ($user, $data): VideoProject {
            $product = $this->product($data->productId);
            $context = $this->context($data, $product);
            $script = $data->prompt !== ''
                ? $data->prompt
                : $this->sceneGeneration->generateMarketingScript($context);

            $project = $this->projects->create([
                'uuid' => (string) Str::uuid(),
                'user_id' => $user->id,
                'product_id' => $product?->id,
                'title' => $this->title($product, $data),
                'language' => $data->language,
                'tone' => $data->template,
                'style' => $data->style,
                'aspect_ratio' => $data->aspectRatio,
                'duration_seconds' => $data->durationSeconds,
                'ai_model' => $data->provider,
                'prompt' => $script,
                'optimized_prompt' => $script,
                'settings' => $data->toProjectSettings(),
                'status' => VideoProjectStatus::Ready,
            ]);

            $this->createScenes($project, $script, $context);

            if ($data->renderImmediately) {
                $this->renderJobs->dispatch($project, ['aspect_ratio' => $data->aspectRatio]);
            }

            return $project->load(['product', 'scenes']);
        });
    }

    private function product(?int $productId): ?Product
    {
        if (!$productId) {
            return null;
        }

        return Product::query()
            ->whereKey($productId)
            ->where('sku', '<>', 'AI-DEMO-RICE-001')
            ->first();
    }

    private function context(VideoGenerationRequestData $data, ?Product $product): array
    {
        return [
            'product_name' => $product?->name ?? 'AI video product',
            'product_brief' => $product?->seo_description ?? $product?->category ?? $data->template,
            'language' => $data->language,
            'style' => $data->style,
            'tone' => $data->template,
            'aspect_ratio' => $data->aspectRatio,
            'camera' => 'dolly_in',
            'character' => in_array($data->template, ['faceless_story', 'reddit_story', 'quote_video'], true) ? 'none' : 'presenter',
            'gender' => 'neutral',
        ];
    }

    private function createScenes(VideoProject $project, string $script, array $context): void
    {
        $transitionIds = Transition::query()->pluck('id', 'slug');

        foreach ($this->sceneGeneration->generateScenes($script, $context) as $scene) {
            $project->scenes()->create([
                'transition_id' => $transitionIds[$scene['transition_type']] ?? null,
                'sort_order' => $scene['sort_order'],
                'title' => $scene['title'],
                'cinematic_description' => $scene['cinematic_description'],
                'voice_over_text' => $scene['voice_over_text'],
                'subtitle_text' => $scene['subtitle_text'],
                'duration_seconds' => $scene['duration_seconds'],
                'camera_movement' => $scene['camera_movement'],
                'animation_style' => $scene['animation_style'],
                'status' => VideoSceneStatus::Ready,
                'metadata' => [
                    'ai_prompt' => $scene['ai_prompt'],
                    'image_prompt' => $scene['image_prompt'],
                    'video_prompt' => $scene['video_prompt'],
                    'transition_type' => $scene['transition_type'],
                ],
            ]);
        }
    }

    private function title(?Product $product, VideoGenerationRequestData $data): string
    {
        $subject = $product?->name ?: Str::headline(str_replace('_', ' ', $data->template));

        return 'AI Video - ' . $subject . ' - ' . now()->format('d/m/Y H:i');
    }
}
