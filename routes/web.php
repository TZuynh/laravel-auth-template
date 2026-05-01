<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ActivityNotificationController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', function (string $locale) {
    if (in_array($locale, ['vi', 'en'], true)) {
        session(['locale' => $locale]);
    }

    return back();
})->name('locale.switch');

Route::middleware('locale')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });

    Route::middleware('guest')->group(function () {
        Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AuthController::class, 'login'])->name('login.post');
        Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
        Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

        Route::post('/ai/chat', [AiChatController::class, 'chat'])
            ->middleware('throttle:30,1')
            ->name('ai.chat');

        Route::delete('/notifications/{notification}', [ActivityNotificationController::class, 'destroy'])
            ->name('notifications.destroy');
        Route::delete('/notifications', [ActivityNotificationController::class, 'clear'])
            ->name('notifications.clear');

        Route::get('/settings', [SettingsController::class, 'index'])
            ->middleware('admin')
            ->name('settings.index');
        Route::post('/settings/cache-clear', [SettingsController::class, 'clearCache'])
            ->middleware('admin')
            ->name('settings.cache-clear');
        Route::post('/settings/integrations', [SettingsController::class, 'updateIntegrations'])
            ->middleware('admin')
            ->name('settings.integrations.update');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::resource('users', UserController::class)
            ->except(['index', 'show'])
            ->middleware('admin');

        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::post('products/export', [ProductController::class, 'export'])
            ->middleware('admin')
            ->name('products.export');
        Route::get('products/export-preview', [ProductController::class, 'exportPreview'])
            ->middleware('admin')
            ->name('products.export-preview');
        Route::get('products/export-status/{productExport}', [ProductController::class, 'exportStatus'])
            ->middleware('admin')
            ->name('products.export-status');
        Route::post('products/export-cancel/{productExport}', [ProductController::class, 'cancelExport'])
            ->middleware('admin')
            ->name('products.export-cancel');
        Route::get('products/export-download/{productExport}', [ProductController::class, 'downloadExport'])
            ->middleware('admin')
            ->name('products.export-download');
        Route::delete('products/clear', [ProductController::class, 'destroyAll'])
            ->middleware('admin')
            ->name('products.clear');
        Route::resource('products', ProductController::class)
            ->except(['index', 'show'])
            ->middleware('admin');
        Route::post('products/import', [ProductController::class, 'import'])
            ->middleware('admin')
            ->name('products.import');

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
