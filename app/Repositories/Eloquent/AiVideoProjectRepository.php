<?php

namespace App\Repositories\Eloquent;

use App\Models\VideoProject;
use App\Repositories\Contracts\AiVideoProjectRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AiVideoProjectRepository implements AiVideoProjectRepositoryInterface
{
    public function create(array $attributes): VideoProject
    {
        return VideoProject::create($attributes);
    }

    public function findOwned(int $projectId, int $userId): ?VideoProject
    {
        return VideoProject::query()
            ->whereKey($projectId)
            ->where('user_id', $userId)
            ->first();
    }

    public function recentForUser(int $userId, int $limit = 20): Collection
    {
        return VideoProject::query()
            ->with(['product', 'renderJobs', 'exports'])
            ->where('user_id', $userId)
            ->latest('id')
            ->limit($limit)
            ->get();
    }
}

