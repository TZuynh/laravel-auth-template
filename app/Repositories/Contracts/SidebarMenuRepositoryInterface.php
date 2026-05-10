<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface SidebarMenuRepositoryInterface
{
    public function groupedForUser(?User $user): array;
}
