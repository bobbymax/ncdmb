<?php

namespace App\Providers;

use App\Repositories\DocumentActionRepository;
use App\Repositories\MailingListRepository;
use App\Repositories\ProgressTrackerRepository;
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
            $documentActionRepository = $app->make(DocumentActionRepository::class);
            $mailingListRepository = $app->make(MailingListRepository::class);
            $progressTrackerRepository = $app->make(ProgressTrackerRepository::class);

            return new WorkflowService($workflowRepository, $documentActionRepository, $mailingListRepository, $progressTrackerRepository);
        });
    }
}
