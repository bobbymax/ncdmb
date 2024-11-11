<?php

namespace App\Providers;

use App\Http\Resources\BudgetCodeResource;
use Illuminate\Support\ServiceProvider;
use App\Repositories\BudgetCodeRepository;
use App\Services\BudgetCodeService;

class BudgetCodeServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the BudgetCodeRepository to BudgetCodeService
        $this->app->bind(BudgetCodeService::class, function ($app) {
            $budgetCodeRepository = $app->make(BudgetCodeRepository::class);
            $budgetCodeResource = $app->make(BudgetCodeResource::class);
            return new BudgetCodeService($budgetCodeRepository, $budgetCodeResource);
        });
    }
}
