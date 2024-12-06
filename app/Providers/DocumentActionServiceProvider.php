<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\DocumentActionRepository;
use App\Services\DocumentActionService;

class DocumentActionServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the DocumentActionRepository to DocumentActionService
        $this->app->bind(DocumentActionService::class, function ($app) {
            $documentActionRepository = $app->make(DocumentActionRepository::class);

            return new DocumentActionService($documentActionRepository);
        });
    }
}
