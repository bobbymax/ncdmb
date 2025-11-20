<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\LegalDocumentRepository;
use App\Services\LegalDocumentService;

class LegalDocumentServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the LegalDocumentRepository to LegalDocumentService
        $this->app->bind(LegalDocumentService::class, function ($app) {
            $legalDocumentRepository = $app->make(LegalDocumentRepository::class);

            return new LegalDocumentService($legalDocumentRepository);
        });
    }
}
