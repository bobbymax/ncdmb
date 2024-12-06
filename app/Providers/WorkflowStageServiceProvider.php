<?php

namespace App\Providers;

use App\Repositories\DocumentActionRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\WorkflowStageRepository;
use App\Services\WorkflowStageService;

class WorkflowStageServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the WorkflowStageRepository to WorkflowStageService
        $this->app->bind(WorkflowStageService::class, function ($app) {
            $workflowStageRepository = $app->make(WorkflowStageRepository::class);
            $documentActionRepository = $app->make(DocumentActionRepository::class);

            return new WorkflowStageService($workflowStageRepository, $documentActionRepository);
        });
    }
}
