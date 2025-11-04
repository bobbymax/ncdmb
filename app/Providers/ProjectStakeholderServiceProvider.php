<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectStakeholderRepository;
use App\Services\ProjectStakeholderService;

class ProjectStakeholderServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProjectStakeholderRepository to ProjectStakeholderService
        $this->app->bind(ProjectStakeholderService::class, function ($app) {
            $projectStakeholderRepository = $app->make(ProjectStakeholderRepository::class);

            return new ProjectStakeholderService($projectStakeholderRepository);
        });
    }
}
