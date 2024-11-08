<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\RenderedServiceRepository;
use App\Services\RenderedServiceService;

class RenderedServiceServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the RenderedServiceRepository to RenderedServiceService
        $this->app->bind(RenderedServiceService::class, function ($app) {
            return new RenderedServiceService($app->make(RenderedServiceRepository::class));
        });
    }
}
