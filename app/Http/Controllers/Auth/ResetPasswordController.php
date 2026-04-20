<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Repositories\Contracts\AuthRepositoryInterface;

class ResetPasswordController extends Controller
{
    public function showResetForm(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function reset(ResetPasswordRequest $request, AuthRepositoryInterface $authRepository)
    {
        $status = $authRepository->resetPassword($request->only('email', 'password', 'password_confirmation', 'token'));

        return $status === 'passwords.reset'
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
