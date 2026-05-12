<?php

namespace App\Services\Marketing;

use App\Models\BrainMemory;
use App\Models\User;
use Illuminate\Support\Str;

class BrainTrainingService
{
    public function store(User $user, array $data): BrainMemory
    {
        return BrainMemory::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $user->id,
            'category' => (string) ($data['category'] ?? 'voice_style'),
            'topic' => trim((string) ($data['topic'] ?? '')) ?: null,
            'content' => trim((string) $data['content']),
            'metadata' => [
                'source' => 'manual_training',
                'locale' => app()->getLocale(),
            ],
        ]);
    }
}
