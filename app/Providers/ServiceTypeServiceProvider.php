<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ServiceTypeRepository;
use App\Services\ServiceTypeService;

class ServiceTypeServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ServiceTypeRepository to ServiceTypeService
        $this->app->bind(ServiceTypeService::class, function ($app) {
            return new ServiceTypeService($app->make(ServiceTypeRepository::class));
        });
    }
}
