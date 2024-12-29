<?php

namespace App\Providers;

use App\Repositories\AllowanceRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\TripCategoryRepository;
use App\Services\TripCategoryService;

class TripCategoryServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the TripCategoryRepository to TripCategoryService
        $this->app->bind(TripCategoryService::class, function ($app) {
            $tripCategoryRepository = $app->make(TripCategoryRepository::class);
            $allowanceRepository = $app->make(AllowanceRepository::class);

            return new TripCategoryService($tripCategoryRepository, $allowanceRepository);
        });
    }
}
