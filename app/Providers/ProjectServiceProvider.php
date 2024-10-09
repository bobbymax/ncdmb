<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectRepository;
use App\Services\ProjectService;

class ProjectServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ProjectRepository to ProjectService
        $this->app->bind(ProjectService::class, function ($app) {
            return new ProjectService($app->make(ProjectRepository::class));
        });
    }
}
