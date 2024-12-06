<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\WorkflowRepository;
use App\Services\WorkflowService;

class WorkflowServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the WorkflowRepository to WorkflowService
        $this->app->bind(WorkflowService::class, function ($app) {
            $workflowRepository = $app->make(WorkflowRepository::class);

            return new WorkflowService($workflowRepository);
        });
    }
}
