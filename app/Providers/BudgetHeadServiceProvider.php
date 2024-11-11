<?php

namespace App\Providers;

use App\Http\Resources\BudgetHeadResource;
use Illuminate\Support\ServiceProvider;
use App\Repositories\BudgetHeadRepository;
use App\Services\BudgetHeadService;

class BudgetHeadServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the BudgetHeadRepository to BudgetHeadService
        $this->app->bind(BudgetHeadService::class, function ($app) {
            $budgetHeadRepository = $app->make(BudgetHeadRepository::class);
            $budgetHeadResource = $app->make(BudgetHeadResource::class);
            return new BudgetHeadService($budgetHeadRepository, $budgetHeadResource);
        });
    }
}
