<?php

namespace App\Providers;

use App\Repositories\ExpenseRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\TripRepository;
use App\Services\TripService;

class TripServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the TripRepository to TripService
        $this->app->bind(TripService::class, function ($app) {
            $tripRepository = $app->make(TripRepository::class);
            $expenseRepository = $app->make(ExpenseRepository::class);

            return new TripService($tripRepository, $expenseRepository);
        });
    }
}
