<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request, AuthRepositoryInterface $authRepository)
    {
        $credentials = $request->only('email', 'password');

        if ($authRepository->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => __('messages.auth.failed'),
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request, AuthRepositoryInterface $authRepository)
    {
        $user = $authRepository->register($request->validated());

        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request, AuthRepositoryInterface $authRepository)
    {
        $authRepository->logout($request);

        return redirect()->route('login')->with('success', __('messages.auth.logged_out'));
    }

    public function dashboard()
    {
        return view('dashboard');
    }
}
