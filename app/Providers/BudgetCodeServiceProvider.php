<?php

namespace App\Providers;

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
    public function register()
    {
        // Bind the BudgetCodeRepository to BudgetCodeService
        $this->app->bind(BudgetCodeService::class, function ($app) {
            return new BudgetCodeService($app->make(BudgetCodeRepository::class));
        });
    }
}
