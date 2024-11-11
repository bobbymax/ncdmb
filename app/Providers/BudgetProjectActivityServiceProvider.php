<?php

namespace App\Providers;

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
    public function register()
    {
        // Bind the BudgetProjectActivityRepository to BudgetProjectActivityService
        $this->app->bind(BudgetProjectActivityService::class, function ($app) {
            return new BudgetProjectActivityService($app->make(BudgetProjectActivityRepository::class));
        });
    }
}
