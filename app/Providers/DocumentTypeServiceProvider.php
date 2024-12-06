<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\DocumentTypeRepository;
use App\Services\DocumentTypeService;

class DocumentTypeServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the DocumentTypeRepository to DocumentTypeService
        $this->app->bind(DocumentTypeService::class, function ($app) {
            $documentTypeRepository = $app->make(DocumentTypeRepository::class);

            return new DocumentTypeService($documentTypeRepository);
        });
    }
}
