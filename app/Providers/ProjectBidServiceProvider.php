<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectBidRepository;
use App\Services\ProjectBidService;

class ProjectBidServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProjectBidRepository to ProjectBidService
        $this->app->bind(ProjectBidService::class, function ($app) {
            $projectBidRepository = $app->make(ProjectBidRepository::class);

            return new ProjectBidService($projectBidRepository);
        });
    }
}
