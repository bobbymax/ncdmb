<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\BoardProjectActivityRepository;
use App\Services\BoardProjectActivityService;

class BoardProjectActivityServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the BoardProjectActivityRepository to BoardProjectActivityService
        $this->app->bind(BoardProjectActivityService::class, function ($app) {
            return new BoardProjectActivityService($app->make(BoardProjectActivityRepository::class));
        });
    }
}
