<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ChartOfAccountRepository;
use App\Services\ChartOfAccountService;

class ChartOfAccountServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ChartOfAccountRepository to ChartOfAccountService
        $this->app->bind(ChartOfAccountService::class, function ($app) {
            $chartOfAccountRepository = $app->make(ChartOfAccountRepository::class);

            return new ChartOfAccountService($chartOfAccountRepository);
        });
    }
}
