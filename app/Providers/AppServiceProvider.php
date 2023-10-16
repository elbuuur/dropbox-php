<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\User\Repositories\UserRepositoryInterface;
use App\Modules\User\Repositories\UserRepository;
use App\Modules\Folder\Repositories\FolderRepository;
use App\Modules\Folder\Repositories\FolderRepositoryInterface;
use App\Modules\File\Repositories\FileRepositoryInterface;
use App\Modules\File\Repositories\FileRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(FolderRepositoryInterface::class, FolderRepository::class);
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
