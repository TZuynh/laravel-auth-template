<?php

namespace App\Http\Resources;

use App\DTOs\AiVideo\BulkVideoOutputData;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VideoVersionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $output = BulkVideoOutputData::fromVersion($this->resource)->toArray();

        return array_replace($output, [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'style_slug' => $this->style_slug,
            'platform' => $this->platform,
            'status' => $this->status,
            'progress' => $this->progress,
            'output_url' => $this->output_url,
            'error_message' => $this->error_message,
        ]);
    }
}
