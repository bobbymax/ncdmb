<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProcurementAuditTrailRepository;
use App\Services\ProcurementAuditTrailService;

class ProcurementAuditTrailServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProcurementAuditTrailRepository to ProcurementAuditTrailService
        $this->app->bind(ProcurementAuditTrailService::class, function ($app) {
            $procurementAuditTrailRepository = $app->make(ProcurementAuditTrailRepository::class);

            return new ProcurementAuditTrailService($procurementAuditTrailRepository);
        });
    }
}
