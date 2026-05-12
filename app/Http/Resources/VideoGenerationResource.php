<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoGenerationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'title' => $this->title,
            'prompt' => $this->prompt,
            'language' => $this->language,
            'aspect_ratio' => $this->aspect_ratio,
            'duration_seconds' => $this->duration_seconds,
            'provider' => $this->provider,
            'render_provider' => $this->render_provider,
            'status' => $this->status,
            'requested_versions' => $this->requested_versions,
            'completed_versions' => $this->completed_versions,
            'failed_versions' => $this->failed_versions,
            'versions' => VideoVersionResource::collection($this->whenLoaded('versions')),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
