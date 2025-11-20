<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ContractDisputeRepository;
use App\Services\ContractDisputeService;

class ContractDisputeServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ContractDisputeRepository to ContractDisputeService
        $this->app->bind(ContractDisputeService::class, function ($app) {
            $contractDisputeRepository = $app->make(ContractDisputeRepository::class);

            return new ContractDisputeService($contractDisputeRepository);
        });
    }
}
