<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectProgramRepository;
use App\Services\ProjectProgramService;

class ProjectProgramServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProjectProgramRepository to ProjectProgramService
        $this->app->bind(ProjectProgramService::class, function ($app) {
            $projectProgramRepository = $app->make(ProjectProgramRepository::class);

            return new ProjectProgramService($projectProgramRepository);
        });
    }
}
