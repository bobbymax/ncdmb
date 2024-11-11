<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectContractRepository;
use App\Services\ProjectContractService;

class ProjectContractServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ProjectContractRepository to ProjectContractService
        $this->app->bind(ProjectContractService::class, function ($app) {
            return new ProjectContractService($app->make(ProjectContractRepository::class));
        });
    }
}
