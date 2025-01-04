<?php

namespace App\Providers;

use App\Repositories\UploadRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\WorkflowStageCategoryRepository;
use App\Services\WorkflowStageCategoryService;

class WorkflowStageCategoryServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the WorkflowStageCategoryRepository to WorkflowStageCategoryService
        $this->app->bind(WorkflowStageCategoryService::class, function ($app) {
            $workflowStageCategoryRepository = $app->make(WorkflowStageCategoryRepository::class);
            $uploadRepository = $app->make(UploadRepository::class);

            return new WorkflowStageCategoryService($workflowStageCategoryRepository, $uploadRepository);
        });
    }
}
