<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function paginateBySearch(?string $query, int $perPage = 10, ?string $role = null, ?string $status = null): LengthAwarePaginator
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

        $role = strtolower(trim((string) $role));
        if ($role !== '' && $role !== 'all') {
            if ($role === 'admin') {
                $builder->whereIn('role', ['administrator', 'admin']);
            } else {
                $builder->where('role', $role);
            }
        }

        $status = strtolower(trim((string) $status));
        if ($status === 'active') {
            $builder->whereNotNull('email_verified_at');
        } elseif ($status === 'locked') {
            $builder->whereNull('email_verified_at');
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
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return $user->refresh();
    }

    public function delete(User $user): bool|null
    {
        return $user->delete();
    }
}
