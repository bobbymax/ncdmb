<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\DocumentTrailRepository;
use App\Services\DocumentTrailService;

class DocumentTrailServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the DocumentTrailRepository to DocumentTrailService
        $this->app->bind(DocumentTrailService::class, function ($app) {
            $documentTrailRepository = $app->make(DocumentTrailRepository::class);

            return new DocumentTrailService($documentTrailRepository);
        });
    }
}
