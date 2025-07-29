<?php

namespace App\Providers;

use App\Repositories\Composites\GameCompositeRepository;
use App\Repositories\Composites\RoomCompositeRepository;
use App\Repositories\Interfaces\GameRepositoryInterface;
use App\Repositories\Interfaces\RoomCompositesRepositoryInterface;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            RoomCompositesRepositoryInterface::class,
            RoomCompositeRepository::class,
        );
        $this->app->bind(
            GameRepositoryInterface::class,
            GameCompositeRepository::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
