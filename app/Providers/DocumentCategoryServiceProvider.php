<?php

namespace App\Providers;

use App\Repositories\DocumentRequirementRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\DocumentCategoryRepository;
use App\Services\DocumentCategoryService;

class DocumentCategoryServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the DocumentCategoryRepository to DocumentCategoryService
        $this->app->bind(DocumentCategoryService::class, function ($app) {
            $documentCategoryRepository = $app->make(DocumentCategoryRepository::class);
            $documentRequirementRepository = $app->make(DocumentRequirementRepository::class);
            return new DocumentCategoryService($documentCategoryRepository, $documentRequirementRepository);
        });
    }
}
