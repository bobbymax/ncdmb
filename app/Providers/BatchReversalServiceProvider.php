<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\BatchReversalRepository;
use App\Services\BatchReversalService;

class BatchReversalServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the BatchReversalRepository to BatchReversalService
        $this->app->bind(BatchReversalService::class, function ($app) {
            return new BatchReversalService($app->make(BatchReversalRepository::class));
        });
    }
}
