<?php

namespace App\Infrastructure\Providers;

use App\Infrastructure\Repositories\Contracts\TransactionRepositoryInterface;
use App\Infrastructure\Repositories\TransactionRepository;
use App\Infrastructure\Services\BusinessCloudService;
use App\Infrastructure\Services\Contracts\BusinessClodServiceContract;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            TransactionRepositoryInterface::class,
            TransactionRepository::class
        );
        $this->app->bind(
            BusinessClodServiceContract::class,
            BusinessCloudService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
