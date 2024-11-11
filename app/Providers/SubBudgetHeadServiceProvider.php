<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\SubBudgetHeadRepository;
use App\Services\SubBudgetHeadService;

class SubBudgetHeadServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the SubBudgetHeadRepository to SubBudgetHeadService
        $this->app->bind(SubBudgetHeadService::class, function ($app) {
            return new SubBudgetHeadService($app->make(SubBudgetHeadRepository::class));
        });
    }
}
