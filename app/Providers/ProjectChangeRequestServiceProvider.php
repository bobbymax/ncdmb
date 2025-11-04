<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectChangeRequestRepository;
use App\Services\ProjectChangeRequestService;

class ProjectChangeRequestServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProjectChangeRequestRepository to ProjectChangeRequestService
        $this->app->bind(ProjectChangeRequestService::class, function ($app) {
            $projectChangeRequestRepository = $app->make(ProjectChangeRequestRepository::class);

            return new ProjectChangeRequestService($projectChangeRequestRepository);
        });
    }
}
