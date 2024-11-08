<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\LifInstitutionServiceRepository;
use App\Services\LifInstitutionServiceService;

class LifInstitutionServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the LifInstitutionServiceRepository to LifInstitutionServiceService
        $this->app->bind(LifInstitutionServiceService::class, function ($app) {
            return new LifInstitutionServiceService($app->make(LifInstitutionServiceRepository::class));
        });
    }
}
