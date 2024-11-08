<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\BoardProjectUtilizationRepository;
use App\Services\BoardProjectUtilizationService;

class BoardProjectUtilizationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the BoardProjectUtilizationRepository to BoardProjectUtilizationService
        $this->app->bind(BoardProjectUtilizationService::class, function ($app) {
            return new BoardProjectUtilizationService($app->make(BoardProjectUtilizationRepository::class));
        });
    }
}
