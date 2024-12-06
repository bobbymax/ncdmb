<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\DocumentCommentRepository;
use App\Services\DocumentCommentService;

class DocumentCommentServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the DocumentCommentRepository to DocumentCommentService
        $this->app->bind(DocumentCommentService::class, function ($app) {
            $documentCommentRepository = $app->make(DocumentCommentRepository::class);

            return new DocumentCommentService($documentCommentRepository);
        });
    }
}
