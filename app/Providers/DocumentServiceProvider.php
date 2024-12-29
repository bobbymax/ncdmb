<?php

namespace App\Providers;

use App\Repositories\DepartmentRepository;
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

            return new DocumentService($documentRepository, $departmentRepository);
        });
    }
}
