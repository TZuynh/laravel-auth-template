<?php

namespace App\Repositories\Contracts;

interface RoleRepositoryInterface
{
    public function permissionMatrix(): array;
}
