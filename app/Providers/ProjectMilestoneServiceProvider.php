<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectMilestoneRepository;
use App\Services\ProjectMilestoneService;

class ProjectMilestoneServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ProjectMilestoneRepository to ProjectMilestoneService
        $this->app->bind(ProjectMilestoneService::class, function ($app) {
            return new ProjectMilestoneService($app->make(ProjectMilestoneRepository::class));
        });
    }
}
