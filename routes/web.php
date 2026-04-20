<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Chuyển hướng trang chủ về login
Route::get('/', function () {
    return redirect()->route('login');
});

// Guest Routes (Chưa đăng nhập)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Auth Routes (Đã đăng nhập)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    // AI Chat (Database-only)
    Route::post('/ai/chat', [AiChatController::class, 'chat'])
        ->middleware('throttle:30,1')
        ->name('ai.chat');
    
    // Quản lý Users
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::resource('users', UserController::class)
        ->except(['index', 'show'])
        ->middleware('admin');
    
    // Hồ sơ cá nhân
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Đăng xuất
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
