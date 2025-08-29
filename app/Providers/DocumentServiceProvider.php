<?php

namespace App\Providers;


use App\Repositories\DepartmentRepository;
use App\Repositories\ProgressTrackerRepository;
use App\Repositories\SignatoryRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use App\Repositories\WorkflowRepository;
use App\Repositories\WorkflowStageRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\DocumentRepository;
use App\Services\DocumentService;

class DocumentServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the DocumentRepository to DocumentService
        $this->app->bind(DocumentService::class, function ($app) {
            $documentRepository = $app->make(DocumentRepository::class);
            $departmentRepository = $app->make(DepartmentRepository::class);
            $progressTracker = $app->make(ProgressTrackerRepository::class);
            $workflowRepository = $app->make(WorkflowRepository::class);
            $userRepository = $app->make(UserRepository::class);
            $signatoryRepository = $app->make(SignatoryRepository::class);
            $workflowStageRepository = $app->make(WorkflowStageRepository::class);
            $uploadRepository = $app->make(UploadRepository::class);

            return new DocumentService(
                $documentRepository,
                $departmentRepository,
                $progressTracker,
                $workflowRepository,
                $userRepository,
                $signatoryRepository,
                $workflowStageRepository,
                $uploadRepository,
            );
        });
    }
}
