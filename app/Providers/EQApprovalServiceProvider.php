<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\EQApprovalRepository;
use App\Services\EQApprovalService;

class EQApprovalServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the EQApprovalRepository to EQApprovalService
        $this->app->bind(EQApprovalService::class, function ($app) {
            return new EQApprovalService($app->make(EQApprovalRepository::class));
        });
    }
}
