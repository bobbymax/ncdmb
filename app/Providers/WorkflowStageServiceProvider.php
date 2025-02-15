<?php

namespace App\Providers;

use App\Repositories\DocumentActionRepository;
use App\Repositories\DocumentRequirementRepository;
use App\Repositories\GroupRepository;
use App\Repositories\MailingListRepository;
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
            $groupRepository = $app->make(GroupRepository::class);
            $documentActionRepository = $app->make(DocumentActionRepository::class);
            $mailingListRepository = $app->make(MailingListRepository::class);
            return new WorkflowStageService($workflowStageRepository, $groupRepository, $documentActionRepository, $mailingListRepository);
        });
    }
}
