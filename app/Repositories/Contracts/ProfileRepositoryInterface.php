<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Http\UploadedFile;

interface ProfileRepositoryInterface
{
    public function update(User $user, array $data, ?UploadedFile $avatar = null): User;
}

