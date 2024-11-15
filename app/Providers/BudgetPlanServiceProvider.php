<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use App\Repositories\BudgetPlanRepository;
use App\Services\BudgetPlanService;

class BudgetPlanServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the BudgetPlanRepository to BudgetPlanService
        $this->app->bind(BudgetPlanService::class, function ($app) {
            $budgetPlanRepository = $app->make(BudgetPlanRepository::class);
            return new BudgetPlanService($budgetPlanRepository);
        });
    }
}
