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
    public function register()
    {
        // Bind the BudgetPlanRepository to BudgetPlanService
        $this->app->bind(BudgetPlanService::class, function ($app) {
            return new BudgetPlanService($app->make(BudgetPlanRepository::class));
        });
    }
}
