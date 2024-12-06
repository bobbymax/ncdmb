<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\DocumentDraftRepository;
use App\Services\DocumentDraftService;

class DocumentDraftServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the DocumentDraftRepository to DocumentDraftService
        $this->app->bind(DocumentDraftService::class, function ($app) {
            $documentDraftRepository = $app->make(DocumentDraftRepository::class);

            return new DocumentDraftService($documentDraftRepository);
        });
    }
}
