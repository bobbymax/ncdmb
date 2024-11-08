<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\EQEmployeeRepository;
use App\Services\EQEmployeeService;

class EQEmployeeServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the EQEmployeeRepository to EQEmployeeService
        $this->app->bind(EQEmployeeService::class, function ($app) {
            return new EQEmployeeService($app->make(EQEmployeeRepository::class));
        });
    }
}
