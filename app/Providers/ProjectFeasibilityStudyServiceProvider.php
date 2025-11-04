<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectFeasibilityStudyRepository;
use App\Services\ProjectFeasibilityStudyService;

class ProjectFeasibilityStudyServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProjectFeasibilityStudyRepository to ProjectFeasibilityStudyService
        $this->app->bind(ProjectFeasibilityStudyService::class, function ($app) {
            $projectFeasibilityStudyRepository = $app->make(ProjectFeasibilityStudyRepository::class);

            return new ProjectFeasibilityStudyService($projectFeasibilityStudyRepository);
        });
    }
}
