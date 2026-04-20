<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;

class AuthRepository implements AuthRepositoryInterface
{
    public function attempt(array $credentials, bool $remember = false): bool
    {
        return Auth::attempt($credentials, $remember);
    }

    public function register(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => 'staff',
        ]);
    }

    public function sendResetLink(array $data): string
    {
        return Password::sendResetLink($data);
    }

    public function resetPassword(array $data): string
    {
        return Password::reset($data, function ($user, $password) {
            $user->forceFill([
                'password' => $password,
            ])->save();
        });
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
