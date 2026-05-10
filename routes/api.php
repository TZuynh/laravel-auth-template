<?php

use App\Http\Controllers\Api\AiVideoProjectController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'throttle:60,1'])
    ->prefix('ai-video')
    ->name('api.ai-video.')
    ->group(function (): void {
        Route::post('/projects', [AiVideoProjectController::class, 'store'])->name('projects.store');
        Route::get('/projects/{videoProject}', [AiVideoProjectController::class, 'show'])->name('projects.show');
        Route::get('/projects/{videoProject}/timeline', [AiVideoProjectController::class, 'timeline'])->name('projects.timeline');
    });

