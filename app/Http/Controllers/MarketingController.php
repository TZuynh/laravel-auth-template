<?php

namespace App\Http\Controllers;

use App\Enums\VideoProjectStatus;
use App\Enums\VideoSceneStatus;
use App\Models\AiImageGeneration;
use App\Models\BrainMemory;
use App\Models\ContentAiDraft;
use App\Models\Export as VideoExport;
use App\Models\Product;
use App\Models\RenderJob;
use App\Models\Transition;
use App\Models\VideoProject;
use App\Repositories\Contracts\MarketingRepositoryInterface;
use App\Services\Marketing\AiImageGenerationService;
use App\Services\Marketing\BrainTrainingService;
use App\Services\Marketing\ContentAiService;
use App\Services\Marketing\EdgeTtsService;
use App\Services\Marketing\SceneGenerationService;
use App\Services\Rendering\RenderArtifactCleanupService;
use App\Services\Rendering\RenderJobDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MarketingController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('marketing.content.index');
    }

    public function content(MarketingRepositoryInterface $marketing): \Illuminate\Contracts\View\View
    {
        return view('marketing.content', [
            'contentHub' => $marketing->contentHubData(),
        ]);
    }

    public function storeContent(Request $request, ContentAiService $content): RedirectResponse
    {
        $data = $request->validate([
            'platform' => ['required', 'string', 'in:facebook,instagram,linkedin,email,zalo,tiktok'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'prompt' => ['nullable', 'string', 'max:3000'],
            'idea' => ['nullable', 'string', 'max:3000'],
            'tone' => ['nullable', 'string', 'max:80'],
            'audience' => ['nullable', 'string', 'max:160'],
            'include_emoji' => ['nullable', 'boolean'],
            'include_hashtags' => ['nullable', 'boolean'],
        ]);

        $draft = $content->generate($request->user(), array_replace($data, [
            'include_emoji' => $request->boolean('include_emoji'),
            'include_hashtags' => $request->boolean('include_hashtags'),
        ]));

        return redirect()
            ->route('marketing.content.index', ['draft' => $draft->id, 'tab' => 'editor'])
            ->with('success', 'Đã tạo bản thảo nội dung AI.');
    }

    public function updateContent(ContentAiDraft $contentDraft, Request $request, ContentAiService $content): RedirectResponse
    {
        $this->authorizeOwnedRecord($contentDraft->user_id);

        $content->update($contentDraft, $request->validate([
            'content' => ['required', 'string', 'max:12000'],
        ]));

        return redirect()
            ->route('marketing.content.index', ['draft' => $contentDraft->id, 'tab' => 'history'])
            ->with('success', 'Đã lưu bản thảo.');
    }

    public function destroyContent(ContentAiDraft $contentDraft): RedirectResponse
    {
        $this->authorizeOwnedRecord($contentDraft->user_id);
        $contentDraft->delete();

        return redirect()->route('marketing.content.index')->with('success', 'Đã xóa bản thảo.');
    }

    public function edgeTts(Request $request, EdgeTtsService $edgeTts): JsonResponse
    {
        $data = $request->validate([
            'text' => ['required', 'string', 'max:12000'],
            'voice' => ['nullable', 'string', 'in:vi-VN-HoaiMyNeural,vi-VN-NamMinhNeural'],
            'tone' => ['nullable', 'string', 'max:80'],
        ]);

        try {
            return response()->json($edgeTts->synthesize(
                $request->user(),
                $data['text'],
                $data['voice'] ?? 'vi-VN-HoaiMyNeural',
                $data['tone'] ?? 'expert'
            ));
        } catch (\Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        }
    }

    public function brain(MarketingRepositoryInterface $marketing): \Illuminate\Contracts\View\View
    {
        return view('marketing.brain', [
            'brain' => $marketing->brainTrainingData(),
        ]);
    }

    public function storeBrainMemory(Request $request, BrainTrainingService $brain): RedirectResponse
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'in:voice_style,usp,faq,offer,customer_insight,brand_rule'],
            'topic' => ['nullable', 'string', 'max:160'],
            'content' => ['required', 'string', 'max:12000'],
        ]);

        $brain->store($request->user(), $data);

        return redirect()->route('marketing.brain.index', ['category' => $data['category']])->with('success', 'Đã lưu vào bộ nhớ AI.');
    }

    public function destroyBrainMemory(BrainMemory $brainMemory): RedirectResponse
    {
        $this->authorizeOwnedRecord($brainMemory->user_id);
        $brainMemory->delete();

        return redirect()->route('marketing.brain.index')->with('success', 'Đã xóa dữ liệu huấn luyện.');
    }

    public function scenes(MarketingRepositoryInterface $marketing): \Illuminate\Contracts\View\View
    {
        return view('marketing.scenes', [
            'sceneEditor' => $marketing->sceneEditorData(),
        ]);
    }

    public function images(MarketingRepositoryInterface $marketing): \Illuminate\Contracts\View\View
    {
        return view('marketing.images', [
            'imageStudio' => $marketing->aiImageStudioData(),
        ]);
    }

    public function renderHistory(MarketingRepositoryInterface $marketing): \Illuminate\Contracts\View\View
    {
        return view('marketing.render-history', [
            'renderHistory' => $marketing->renderHistoryData(),
        ]);
    }

    public function exports(MarketingRepositoryInterface $marketing): \Illuminate\Contracts\View\View
    {
        return view('marketing.exports', [
            'exportManager' => $marketing->exportManagerData(),
        ]);
    }

    public function templates(MarketingRepositoryInterface $marketing): \Illuminate\Contracts\View\View
    {
        return view('marketing.templates', [
            'templateManager' => $marketing->templateManagerData(),
        ]);
    }

    public function storeProject(
        Request $request,
        SceneGenerationService $sceneEngine,
        RenderJobDispatcher $renderer,
    ): RedirectResponse {
        $data = $request->validate([
            'product_id' => ['nullable', 'integer'],
            'ai_model' => ['nullable', 'string', 'max:120'],
            'style' => ['nullable', 'string', 'max:60'],
            'duration' => ['nullable', 'numeric', 'min:5', 'max:60'],
            'camera' => ['nullable', 'string', 'max:80'],
            'aspect_ratio' => ['nullable', 'string', 'in:9:16,16:9,1:1'],
            'character' => ['nullable', 'string', 'max:60'],
            'gender' => ['nullable', 'string', 'max:30'],
            'voice' => ['nullable', 'string', 'max:80'],
            'music' => ['nullable', 'string', 'max:80'],
            'prompt' => ['nullable', 'string', 'max:5000'],
            'intent' => ['nullable', 'string', 'in:scenes,render'],
        ]);

        $product = !empty($data['product_id']) && (int) $data['product_id'] > 0
            ? Product::query()
                ->whereKey($data['product_id'])
                ->where('sku', '<>', 'AI-DEMO-RICE-001')
                ->where(function ($query): void {
                    $query->whereNull('name')->orWhere('name', 'not like', '%Gạo Thuần%');
                })
                ->first()
            : null;
        $script = trim((string) ($data['prompt'] ?? ''));
        $context = [
            'product_name' => $product?->name ?? 'Luxury Product',
            'product_brief' => $product?->seo_description ?? $product?->category,
            'language' => app()->getLocale(),
            'style' => $data['style'] ?? 'cinematic',
            'tone' => 'premium',
            'aspect_ratio' => $data['aspect_ratio'] ?? '9:16',
            'camera' => $data['camera'] ?? 'dolly_in',
            'character' => $data['character'] ?? 'none',
            'gender' => $data['gender'] ?? 'neutral',
        ];

        if ($script === '') {
            $script = $sceneEngine->generateMarketingScript($context);
        }

        $project = VideoProject::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'product_id' => $product?->id,
            'title' => 'AI Video - ' . ($product?->name ?? now()->format('d/m/Y H:i')),
            'language' => app()->getLocale(),
            'tone' => 'premium',
            'style' => $data['style'] ?? 'cinematic',
            'aspect_ratio' => $data['aspect_ratio'] ?? '9:16',
            'duration_seconds' => $data['duration'] ?? 12,
            'ai_model' => $data['ai_model'] ?? null,
            'prompt' => $script,
            'optimized_prompt' => $script,
            'settings' => [
                'camera' => $data['camera'] ?? null,
                'character' => $data['character'] ?? null,
                'gender' => $data['gender'] ?? null,
                'voice' => $data['voice'] ?? 'female_south',
                'music' => $data['music'] ?? 'tiktok',
            ],
            'status' => VideoProjectStatus::Ready,
        ]);

        $transitionIds = Transition::query()->pluck('id', 'slug');
        foreach ($sceneEngine->generateScenes($script, $context) as $scene) {
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

        if (($data['intent'] ?? 'scenes') === 'render') {
            $renderJob = $renderer->dispatch($project, ['aspect_ratio' => $project->aspect_ratio->value]);

            if (($renderJob->status?->value ?? $renderJob->status) === 'failed') {
                return redirect()->route('marketing.render-history')->with('error', $renderJob->error_message ?: 'Render job failed.');
            }

            return redirect()->route('marketing.render-history')->with('success', 'Đã đưa video vào hàng đợi render MP4.');
        }

        return redirect()->route('marketing.scenes')->with('success', 'Đã tạo 4 cảnh cinematic từ AI Director.');
    }

    public function renderProject(VideoProject $videoProject, RenderJobDispatcher $renderer): RedirectResponse
    {
        $renderJob = $renderer->dispatch($videoProject, ['aspect_ratio' => $videoProject->aspect_ratio->value]);

        if (($renderJob->status?->value ?? $renderJob->status) === 'failed') {
            return redirect()->route('marketing.render-history')->with('error', $renderJob->error_message ?: 'Render job failed.');
        }

        return redirect()->route('marketing.render-history')->with('success', 'Đã đưa video vào hàng đợi render MP4.');
    }

    public function storeImage(Request $request, AiImageGenerationService $images): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'provider' => ['nullable', 'string', 'max:80'],
            'model' => ['nullable', 'string', 'max:120'],
            'style' => ['required', 'string', 'max:80'],
            'aspect_ratio' => ['required', 'string', 'in:9:16,16:9,1:1,4:5'],
            'audience' => ['nullable', 'string', 'max:160'],
            'prompt' => ['nullable', 'string', 'max:3000'],
            'random' => ['nullable', 'boolean'],
        ]);

        $generation = $images->generate($request->user(), array_replace($data, [
            'random' => $request->boolean('random'),
        ]));

        return redirect()
            ->route('marketing.images')
            ->with('success', 'Đã tạo hình ảnh AI: ' . ($generation->metadata['source'] === 'local_fallback' ? 'local fallback' : $generation->provider) . '.');
    }

    public function downloadExport(VideoExport $export)
    {
        abort_if(!$export->file_path || !Storage::disk('public')->exists($export->file_path), 404);

        $filename = Str::slug($export->videoProject?->title ?: 'ai-video-export') . '.mp4';

        return Storage::disk('public')->download($export->file_path, $filename);
    }

    public function destroyRenderJob(RenderJob $renderJob, RenderArtifactCleanupService $cleanup): RedirectResponse
    {
        $cleanup->deleteRenderJob($renderJob);

        return back()->with('success', 'Đã xóa render job và file MP4 liên quan.');
    }

    public function clearCompletedRenderJobs(RenderArtifactCleanupService $cleanup): RedirectResponse
    {
        $result = $cleanup->deleteCompletedJobs();

        return back()->with(
            'success',
            "Đã xóa {$result['jobs']} render job cũ và {$result['files']} file MP4."
        );
    }

    public function destroyExport(VideoExport $export, RenderArtifactCleanupService $cleanup): RedirectResponse
    {
        $cleanup->deleteExport($export);

        return back()->with('success', 'Đã xóa file export MP4.');
    }

    public function destroyImage(AiImageGeneration $aiImageGeneration, AiImageGenerationService $images): RedirectResponse
    {
        $images->delete($aiImageGeneration);

        return back()->with('success', 'Đã xóa hình ảnh AI.');
    }

    private function authorizeOwnedRecord(int $userId): void
    {
        abort_unless(auth()->id() === $userId || auth()->user()?->role === 'admin', 403);
    }
}
