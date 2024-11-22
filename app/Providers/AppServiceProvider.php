<?php

namespace App\Providers;

use App\Repositories\BookingRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\Contracts\BookingRepositoryInterface;
use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\WorkshopRepositoryInterface;
use App\Repositories\WorkshopRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        // Register the category repository
        $this->app->singleton(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
        );

        // Register the workshop repository
        $this->app->singleton(
            WorkshopRepositoryInterface::class,
            WorkshopRepository::class
        );

        // Register the booking repository
        $this->app->singleton(
            BookingRepositoryInterface::class,
            BookingRepository::class
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
