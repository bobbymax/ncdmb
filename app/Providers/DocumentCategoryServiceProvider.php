<?php

namespace App\Providers;

use App\Repositories\BlockRepository;
use App\Repositories\DocumentRequirementRepository;
use App\Repositories\SignatoryRepository;
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
            $blockRepository = $app->make(BlockRepository::class);
            $signatoryRepository = $app->make(SignatoryRepository::class);
            return new DocumentCategoryService($documentCategoryRepository, $documentRequirementRepository, $blockRepository, $signatoryRepository);
        });
    }
}
