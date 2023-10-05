<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\User\Repositories\UserRepositoryInterface;
use App\Modules\User\Repositories\UserRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
