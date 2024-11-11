<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\BoardProjectRepository;
use App\Services\BoardProjectService;

class BoardProjectServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the BoardProjectRepository to BoardProjectService
        $this->app->bind(BoardProjectService::class, function ($app) {
            return new BoardProjectService($app->make(BoardProjectRepository::class));
        });
    }
}
