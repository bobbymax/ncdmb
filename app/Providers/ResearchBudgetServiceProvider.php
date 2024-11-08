<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ResearchBudgetRepository;
use App\Services\ResearchBudgetService;

class ResearchBudgetServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ResearchBudgetRepository to ResearchBudgetService
        $this->app->bind(ResearchBudgetService::class, function ($app) {
            return new ResearchBudgetService($app->make(ResearchBudgetRepository::class));
        });
    }
}
