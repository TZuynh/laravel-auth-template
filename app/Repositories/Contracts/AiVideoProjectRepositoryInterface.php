<?php

namespace App\Repositories\Contracts;

use App\Models\VideoProject;
use Illuminate\Database\Eloquent\Collection;

interface AiVideoProjectRepositoryInterface
{
    public function create(array $attributes): VideoProject;

    public function findOwned(int $projectId, int $userId): ?VideoProject;

    public function recentForUser(int $userId, int $limit = 20): Collection;
}

