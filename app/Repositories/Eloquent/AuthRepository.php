<?php

namespace App\Repositories\Eloquent;

use App\Models\PasswordResetCode;
use App\Models\User;
use App\Notifications\PasswordResetCodeNotification;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthRepository implements AuthRepositoryInterface
{
    private const RESET_CODE_TTL_MINUTES = 10;

    private const RESET_CODE_MAX_ATTEMPTS = 5;

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

    public function sendResetCode(array $data): string
    {
        $email = Str::lower(trim((string) $data['email']));
        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        PasswordResetCode::query()
            ->where('email', $email)
            ->whereNull('consumed_at')
            ->delete();

        if (!$user) {
            return 'password_reset_code.sent';
        }

        $code = (string) random_int(100000, 999999);

        PasswordResetCode::create([
            'email' => $email,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::RESET_CODE_TTL_MINUTES),
        ]);

        $user->notify(new PasswordResetCodeNotification($code, self::RESET_CODE_TTL_MINUTES));

        return 'password_reset_code.sent';
    }

    public function resetPasswordWithCode(array $data): string
    {
        $email = Str::lower(trim((string) $data['email']));
        $code = preg_replace('/\D+/', '', (string) $data['code']);

        $resetCode = PasswordResetCode::query()
            ->where('email', $email)
            ->whereNull('consumed_at')
            ->latest('id')
            ->first();

        if (!$resetCode || $resetCode->expires_at->isPast() || $resetCode->attempts >= self::RESET_CODE_MAX_ATTEMPTS) {
            return 'password_reset_code.invalid';
        }

        if (!Hash::check($code, $resetCode->code_hash)) {
            $resetCode->increment('attempts');

            return 'password_reset_code.invalid';
        }

        $user = User::query()->whereRaw('LOWER(email) = ?', [$email])->first();

        if (!$user) {
            return 'password_reset_code.invalid';
        }

        $user->forceFill([
            'password' => $data['password'],
        ])->save();

        $resetCode->update(['consumed_at' => now()]);

        PasswordResetCode::query()
            ->where('email', $email)
            ->whereNull('consumed_at')
            ->delete();

        return 'password_reset_code.reset';
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
