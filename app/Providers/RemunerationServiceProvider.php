<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\RemunerationRepository;
use App\Services\RemunerationService;

class RemunerationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the RemunerationRepository to RemunerationService
        $this->app->bind(RemunerationService::class, function ($app) {
            return new RemunerationService($app->make(RemunerationRepository::class));
        });
    }
}
