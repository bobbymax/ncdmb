<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\BrokerRepository;
use App\Services\BrokerService;

class BrokerServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the BrokerRepository to BrokerService
        $this->app->bind(BrokerService::class, function ($app) {
            return new BrokerService($app->make(BrokerRepository::class));
        });
    }
}
