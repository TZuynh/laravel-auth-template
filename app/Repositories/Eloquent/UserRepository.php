<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function paginateBySearch(?string $query, int $perPage = 10): LengthAwarePaginator
    {
        $search = trim((string) $query);

        $builder = User::query();

        if ($search !== '') {
            $normalized = strtolower($search);
            $roleFilter = null;

            if (in_array($normalized, ['admin', 'administrator'], true)) {
                $roleFilter = 'administrator';
            } elseif ($normalized === 'staff') {
                $roleFilter = 'staff';
            }

            $builder->where(function ($subQuery) use ($search, $roleFilter) {
                if (ctype_digit($search)) {
                    $subQuery->orWhere('id', (int) $search);
                }

                $subQuery->orWhere('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');

                if ($roleFilter !== null) {
                    $subQuery->orWhere('role', $roleFilter);
                }
            });
        }

        return $builder
            ->orderBy('id', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->refresh();
    }

    public function delete(User $user): bool|null
    {
        return $user->delete();
    }
}

