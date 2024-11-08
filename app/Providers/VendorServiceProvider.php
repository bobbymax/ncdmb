<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\VendorRepository;
use App\Services\VendorService;

class VendorServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the VendorRepository to VendorService
        $this->app->bind(VendorService::class, function ($app) {
            return new VendorService($app->make(VendorRepository::class));
        });
    }
}
