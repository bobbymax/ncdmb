<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\LifInstitutionRepository;
use App\Services\LifInstitutionService;

class LifInstitutionServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the LifInstitutionRepository to LifInstitutionService
        $this->app->bind(LifInstitutionService::class, function ($app) {
            return new LifInstitutionService($app->make(LifInstitutionRepository::class));
        });
    }
}
