<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\DocumentUpdateRepository;
use App\Services\DocumentUpdateService;

class DocumentUpdateServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the DocumentUpdateRepository to DocumentUpdateService
        $this->app->bind(DocumentUpdateService::class, function ($app) {
            $documentUpdateRepository = $app->make(DocumentUpdateRepository::class);

            return new DocumentUpdateService($documentUpdateRepository);
        });
    }
}
