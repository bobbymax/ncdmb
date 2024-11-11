<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\FundRepository;
use App\Services\FundService;

class FundServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the FundRepository to FundService
        $this->app->bind(FundService::class, function ($app) {
            return new FundService($app->make(FundRepository::class));
        });
    }
}
