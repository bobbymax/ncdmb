<?php

namespace App\Providers;

use App\Http\Resources\BudgetProjectActivityResource;
use Illuminate\Support\ServiceProvider;
use App\Repositories\BudgetProjectActivityRepository;
use App\Services\BudgetProjectActivityService;

class BudgetProjectActivityServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the BudgetProjectActivityRepository to BudgetProjectActivityService
        $this->app->bind(BudgetProjectActivityService::class, function ($app) {
            $budgetProjectActivityRepository = $app->make(BudgetProjectActivityRepository::class);
            $budgetProjectActivityResource = $app->make(BudgetProjectActivityResource::class);
            return new BudgetProjectActivityService($budgetProjectActivityRepository, $budgetProjectActivityResource);
        });
    }
}