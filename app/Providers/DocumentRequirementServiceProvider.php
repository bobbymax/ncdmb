<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\DocumentRequirementRepository;
use App\Services\DocumentRequirementService;

class DocumentRequirementServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the DocumentRequirementRepository to DocumentRequirementService
        $this->app->bind(DocumentRequirementService::class, function ($app) {
            $documentRequirementRepository = $app->make(DocumentRequirementRepository::class);

            return new DocumentRequirementService($documentRequirementRepository);
        });
    }
}
