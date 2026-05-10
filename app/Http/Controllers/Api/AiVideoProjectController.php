<?php

namespace App\Http\Controllers\Api;

use App\DTOs\AiVideo\VideoGenerationRequestData;
use App\Http\Controllers\Controller;
use App\Http\Requests\AiVideo\StoreAiVideoProjectRequest;
use App\Models\VideoProject;
use App\Services\AiVideo\AiVideoProjectOrchestrator;
use App\Services\AiVideo\TimelineManifestBuilder;
use Illuminate\Http\JsonResponse;

class AiVideoProjectController extends Controller
{
    public function store(StoreAiVideoProjectRequest $request, AiVideoProjectOrchestrator $orchestrator): JsonResponse
    {
        $project = $orchestrator->create(
            $request->user(),
            VideoGenerationRequestData::fromValidated($request->validated())
        );

        return response()->json([
            'data' => [
                'id' => $project->id,
                'uuid' => $project->uuid,
                'title' => $project->title,
                'status' => $project->status?->value ?? (string) $project->status,
                'scene_count' => $project->scenes->count(),
            ],
        ], 201);
    }

    public function show(VideoProject $videoProject): JsonResponse
    {
        $this->authorizeProject($videoProject);

        $videoProject->load(['product', 'scenes.sceneAssets', 'renderJobs', 'exports']);

        return response()->json([
            'data' => [
                'id' => $videoProject->id,
                'uuid' => $videoProject->uuid,
                'title' => $videoProject->title,
                'product' => $videoProject->product?->name,
                'status' => $videoProject->status?->value ?? (string) $videoProject->status,
                'aspect_ratio' => $videoProject->aspect_ratio?->value ?? (string) $videoProject->aspect_ratio,
                'duration_seconds' => (float) $videoProject->duration_seconds,
                'scenes' => $videoProject->scenes->map(fn ($scene): array => [
                    'id' => $scene->id,
                    'sort_order' => $scene->sort_order,
                    'title' => $scene->title,
                    'duration_seconds' => (float) $scene->duration_seconds,
                    'status' => $scene->status?->value ?? (string) $scene->status,
                ])->values(),
            ],
        ]);
    }

    public function timeline(VideoProject $videoProject, TimelineManifestBuilder $builder): JsonResponse
    {
        $this->authorizeProject($videoProject);

        return response()->json([
            'data' => $builder->build($videoProject)->toArray(),
        ]);
    }

    private function authorizeProject(VideoProject $project): void
    {
        abort_unless(auth()->id() === (int) $project->user_id || auth()->user()?->role === 'admin', 403);
    }
}
