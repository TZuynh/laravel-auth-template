<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Repositories\Contracts\AuthRepositoryInterface;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetCodeEmail(ForgotPasswordRequest $request, AuthRepositoryInterface $authRepository)
    {
        $authRepository->sendResetCode($request->only('email'));

        return redirect()
            ->route('password.reset', ['email' => $request->string('email')->toString()])
            ->with('status', 'A 6-digit reset code has been sent to your email.');
    }
}
