<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\OperationRepository;
use App\Services\OperationService;

class OperationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the OperationRepository to OperationService
        $this->app->bind(OperationService::class, function ($app) {
            $operationRepository = $app->make(OperationRepository::class);

            return new OperationService($operationRepository);
        });
    }
}
