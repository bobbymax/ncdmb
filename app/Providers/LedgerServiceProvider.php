<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\LedgerRepository;
use App\Services\LedgerService;

class LedgerServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the LedgerRepository to LedgerService
        $this->app->bind(LedgerService::class, function ($app) {
            $ledgerRepository = $app->make(LedgerRepository::class);

            return new LedgerService($ledgerRepository);
        });
    }
}
