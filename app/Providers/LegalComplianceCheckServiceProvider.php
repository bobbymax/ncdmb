<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\LegalComplianceCheckRepository;
use App\Services\LegalComplianceCheckService;

class LegalComplianceCheckServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the LegalComplianceCheckRepository to LegalComplianceCheckService
        $this->app->bind(LegalComplianceCheckService::class, function ($app) {
            $legalComplianceCheckRepository = $app->make(LegalComplianceCheckRepository::class);

            return new LegalComplianceCheckService($legalComplianceCheckRepository);
        });
    }
}
