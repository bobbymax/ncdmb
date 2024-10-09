<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectScopeRepository;
use App\Services\ProjectScopeService;

class ProjectScopeServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ProjectScopeRepository to ProjectScopeService
        $this->app->bind(ProjectScopeService::class, function ($app) {
            return new ProjectScopeService($app->make(ProjectScopeRepository::class));
        });
    }
}
