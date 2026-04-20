<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Http\Request;

interface AuthRepositoryInterface
{
    public function attempt(array $credentials, bool $remember = false): bool;

    public function register(array $data): User;

    public function sendResetLink(array $data): string;

    public function resetPassword(array $data): string;

    public function logout(Request $request): void;
}
