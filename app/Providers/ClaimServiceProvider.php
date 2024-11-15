<?php

namespace App\Providers;


use App\Repositories\ExpenseRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\ClaimRepository;
use App\Services\ClaimService;

class ClaimServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ClaimRepository to ClaimService
        $this->app->bind(ClaimService::class, function ($app) {
            $claimRepository = $app->make(ClaimRepository::class);
            $expenseRepository = $app->make(ExpenseRepository::class);
            return new ClaimService($claimRepository, $expenseRepository);
        });
    }
}
