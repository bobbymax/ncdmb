<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\LegalClearanceRepository;
use App\Services\LegalClearanceService;

class LegalClearanceServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the LegalClearanceRepository to LegalClearanceService
        $this->app->bind(LegalClearanceService::class, function ($app) {
            $legalClearanceRepository = $app->make(LegalClearanceRepository::class);

            return new LegalClearanceService($legalClearanceRepository);
        });
    }
}
