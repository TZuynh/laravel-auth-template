<?php

namespace App\Http\Controllers\Api;

use App\DTOs\AiVideo\BulkVideoGenerationRequestData;
use App\DTOs\AiVideo\BulkVideoOutputData;
use App\Http\Controllers\Controller;
use App\Http\Requests\AiVideo\StoreBulkVideoGenerationRequest;
use App\Http\Resources\VideoGenerationResource;
use App\Models\VideoGeneration;
use App\Services\AiVideo\BulkVideoGenerationService;
use Illuminate\Http\JsonResponse;

class BulkAiVideoGenerationController extends Controller
{
    public function store(StoreBulkVideoGenerationRequest $request, BulkVideoGenerationService $service): VideoGenerationResource
    {
        $generation = $service->create(
            $request->user(),
            BulkVideoGenerationRequestData::fromValidated($request->validated())
        );

        return VideoGenerationResource::make($generation->load('versions.videoProject.scenes'));
    }

    public function show(VideoGeneration $videoGeneration): VideoGenerationResource
    {
        $this->authorizeGeneration($videoGeneration);

        return VideoGenerationResource::make($videoGeneration->load('versions.videoProject.scenes'));
    }

    public function output(VideoGeneration $videoGeneration): JsonResponse
    {
        $this->authorizeGeneration($videoGeneration);

        $videoGeneration->load('versions.videoProject.scenes');

        return response()->json([
            'data' => $videoGeneration->versions
                ->map(fn ($version): array => BulkVideoOutputData::fromVersion($version)->toArray())
                ->values(),
        ]);
    }

    private function authorizeGeneration(VideoGeneration $generation): void
    {
        abort_unless(auth()->id() === (int) $generation->user_id || auth()->user()?->role === 'admin', 403);
    }
}
