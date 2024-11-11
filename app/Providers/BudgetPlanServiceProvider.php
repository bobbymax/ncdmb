<?php

namespace App\Providers;

use App\Http\Resources\BudgetPlanResource;
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
            $budgetPlanResource = $app->make(BudgetPlanResource::class);
            return new BudgetPlanService($budgetPlanRepository, $budgetPlanResource);
        });
    }
}
