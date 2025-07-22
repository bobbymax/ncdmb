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
    public function register(): void
    {
        // Bind the ProjectRepository to ProjectService
        $this->app->bind(ProjectService::class, function ($app) {
            $projectRepository = $app->make(ProjectRepository::class);

            return new ProjectService($projectRepository);
        });
    }
}
