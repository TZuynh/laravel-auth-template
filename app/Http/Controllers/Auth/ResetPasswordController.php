<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Repositories\Contracts\AuthRepositoryInterface;

class ResetPasswordController extends Controller
{
    public function showResetForm()
    {
        return view('auth.reset-password', [
            'email' => request()->string('email')->toString(),
        ]);
    }

    public function reset(ResetPasswordRequest $request, AuthRepositoryInterface $authRepository)
    {
        $status = $authRepository->resetPasswordWithCode($request->only('email', 'code', 'password', 'password_confirmation'));

        return $status === 'password_reset_code.reset'
            ? redirect()->route('login')->with('status', 'Your password has been updated. Please sign in.')
            : back()->withErrors(['code' => ['The reset code is invalid or expired.']])->withInput($request->only('email'));
    }
}
