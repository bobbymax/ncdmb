<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectIssueRepository;
use App\Services\ProjectIssueService;

class ProjectIssueServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProjectIssueRepository to ProjectIssueService
        $this->app->bind(ProjectIssueService::class, function ($app) {
            $projectIssueRepository = $app->make(ProjectIssueRepository::class);

            return new ProjectIssueService($projectIssueRepository);
        });
    }
}
