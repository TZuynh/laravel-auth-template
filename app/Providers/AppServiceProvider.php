<?php

namespace App\Providers;

use App\Repositories\Contracts\AuthRepositoryInterface;
use App\Repositories\Contracts\AiVideoProjectRepositoryInterface;
use App\Repositories\Contracts\MarketingRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\ProfileRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\SidebarMenuRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\AuthRepository;
use App\Repositories\Eloquent\AiVideoProjectRepository;
use App\Repositories\Eloquent\MarketingRepository;
use App\Repositories\Eloquent\ProductRepository;
use App\Repositories\Eloquent\ProfileRepository;
use App\Repositories\Eloquent\RoleRepository;
use App\Repositories\Eloquent\SidebarMenuRepository;
use App\Repositories\Eloquent\UserRepository;
use App\Services\ActivityNotificationService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(AiVideoProjectRepositoryInterface::class, AiVideoProjectRepository::class);
        $this->app->bind(MarketingRepositoryInterface::class, MarketingRepository::class);
        $this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(SidebarMenuRepositoryInterface::class, SidebarMenuRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->singleton(ActivityNotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
