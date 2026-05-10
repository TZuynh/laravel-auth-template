<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ActivityNotificationController;
use App\Http\Controllers\AiChatController;
use App\Http\Controllers\ErpController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
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
        Route::get('/dashboard', fn () => redirect()->route('dashboard'));
        Route::get('/admin/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

        Route::prefix('admin')->name('erp.')->group(function () {
            Route::get('/timekeeping', [ErpController::class, 'timekeeping'])->name('timekeeping');
            Route::get('/leaves', [ErpController::class, 'leaves'])->name('leaves');
            Route::get('/my-kpi', [ErpController::class, 'myKpi'])->name('my-kpi');
            Route::get('/approvals', [ErpController::class, 'approvals'])->name('approvals');
            Route::get('/evaluate', [ErpController::class, 'evaluateKpi'])->name('evaluate');
            Route::get('/employees', [ErpController::class, 'employees'])->name('employees');
            Route::get('/payroll', [ErpController::class, 'payroll'])->name('payroll');
            Route::get('/recruitment', [ErpController::class, 'recruitment'])->name('recruitment');
            Route::get('/orders', [ErpController::class, 'contracts'])->name('contracts');
            Route::get('/contracts', fn () => redirect()->route('erp.contracts'))->name('contracts.redirect');
            Route::get('/procurement', [ErpController::class, 'procurementAlerts'])->name('procurement');
            Route::get('/proaurement', fn () => redirect()->route('erp.procurement'))->name('procurement.typo');
            Route::get('/analytics', [ErpController::class, 'analytics'])->name('analytics');
            Route::get('/analytics/live', [ErpController::class, 'analyticsLive'])->name('analytics.live');
            Route::get('/bom', [ProductController::class, 'index'])->name('bom');
            Route::get('/categories', [ErpController::class, 'categories'])->name('categories');
            Route::get('/inventory', [ErpController::class, 'inventory'])->name('inventory');
            Route::get('/purchase-orders', [ErpController::class, 'purchaseOrders'])->name('purchase-orders');
            Route::get('/purdhase-orders', fn () => redirect()->route('erp.purchase-orders'))->name('purchase-orders.typo');
            Route::get('/stock-report', [ErpController::class, 'stockReport'])->name('stock-report');
            Route::get('/xnt-report', fn () => redirect()->route('erp.stock-report'))->name('stock-report.redirect');
        });

        Route::post('/ai/chat', [AiChatController::class, 'chat'])
            ->middleware('throttle:30,1')
            ->name('ai.chat');

        Route::get('/admin/marketing', [MarketingController::class, 'index'])
            ->middleware('admin')
            ->name('marketing.index');
        Route::get('/admin/marketing/scenes', [MarketingController::class, 'scenes'])
            ->middleware('admin')
            ->name('marketing.scenes');
        Route::get('/admin/marketing/images', [MarketingController::class, 'images'])
            ->middleware('admin')
            ->name('marketing.images');
        Route::post('/admin/marketing/images', [MarketingController::class, 'storeImage'])
            ->middleware('admin')
            ->name('marketing.images.store');
        Route::delete('/admin/marketing/images/{aiImageGeneration}', [MarketingController::class, 'destroyImage'])
            ->middleware('admin')
            ->name('marketing.images.destroy');
        Route::get('/admin/marketing/render-history', [MarketingController::class, 'renderHistory'])
            ->middleware('admin')
            ->name('marketing.render-history');
        Route::get('/admin/marketing/exports', [MarketingController::class, 'exports'])
            ->middleware('admin')
            ->name('marketing.exports');
        Route::get('/admin/marketing/templates', [MarketingController::class, 'templates'])
            ->middleware('admin')
            ->name('marketing.templates');
        Route::post('/admin/marketing/projects', [MarketingController::class, 'storeProject'])
            ->middleware('admin')
            ->name('marketing.projects.store');
        Route::post('/admin/marketing/projects/{videoProject}/render', [MarketingController::class, 'renderProject'])
            ->middleware('admin')
            ->name('marketing.projects.render');
        Route::delete('/admin/marketing/render-history/clear-completed', [MarketingController::class, 'clearCompletedRenderJobs'])
            ->middleware('admin')
            ->name('marketing.render-history.clear-completed');
        Route::delete('/admin/marketing/render-history/{renderJob}', [MarketingController::class, 'destroyRenderJob'])
            ->middleware('admin')
            ->name('marketing.render-history.destroy');
        Route::get('/admin/marketing/exports/{export}/download', [MarketingController::class, 'downloadExport'])
            ->middleware('admin')
            ->name('marketing.exports.download');
        Route::delete('/admin/marketing/exports/{export}', [MarketingController::class, 'destroyExport'])
            ->middleware('admin')
            ->name('marketing.exports.destroy');

        Route::delete('/notifications/{notification}', [ActivityNotificationController::class, 'destroy'])
            ->name('notifications.destroy');
        Route::delete('/notifications', [ActivityNotificationController::class, 'clear'])
            ->name('notifications.clear');

        Route::get('/settings', fn () => redirect()->route('settings.index'))
            ->middleware('admin');
        Route::get('/admin/settings', [SettingsController::class, 'index'])
            ->middleware('admin')
            ->name('settings.index');
        Route::post('/admin/settings/cache-clear', [SettingsController::class, 'clearCache'])
            ->middleware('admin')
            ->name('settings.cache-clear');
        Route::post('/admin/settings/integrations', [SettingsController::class, 'updateIntegrations'])
            ->middleware('admin')
            ->name('settings.integrations.update');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('/admin/roles', [RoleController::class, 'index'])
            ->middleware('admin')
            ->name('roles.index');
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
