<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\LegalAuditTrailRepository;
use App\Services\LegalAuditTrailService;

class LegalAuditTrailServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the LegalAuditTrailRepository to LegalAuditTrailService
        $this->app->bind(LegalAuditTrailService::class, function ($app) {
            $legalAuditTrailRepository = $app->make(LegalAuditTrailRepository::class);

            return new LegalAuditTrailService($legalAuditTrailRepository);
        });
    }
}
