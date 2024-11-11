<?php

namespace App\Providers;

use App\Http\Resources\ProjectContractResource;
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
    public function register(): void
    {
        // Bind the ProjectContractRepository to ProjectContractService
        $this->app->bind(ProjectContractService::class, function ($app) {
            $projectContractRepository = $app->make(ProjectContractRepository::class);
            $projectContractResource = $app->make(ProjectContractResource::class);
            return new ProjectContractService($projectContractRepository, $projectContractResource);
        });
    }
}
