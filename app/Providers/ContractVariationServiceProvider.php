<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ContractVariationRepository;
use App\Services\ContractVariationService;

class ContractVariationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ContractVariationRepository to ContractVariationService
        $this->app->bind(ContractVariationService::class, function ($app) {
            $contractVariationRepository = $app->make(ContractVariationRepository::class);

            return new ContractVariationService($contractVariationRepository);
        });
    }
}
