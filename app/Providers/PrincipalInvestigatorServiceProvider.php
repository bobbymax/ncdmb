<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\PrincipalInvestigatorRepository;
use App\Services\PrincipalInvestigatorService;

class PrincipalInvestigatorServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the PrincipalInvestigatorRepository to PrincipalInvestigatorService
        $this->app->bind(PrincipalInvestigatorService::class, function ($app) {
            return new PrincipalInvestigatorService($app->make(PrincipalInvestigatorRepository::class));
        });
    }
}
