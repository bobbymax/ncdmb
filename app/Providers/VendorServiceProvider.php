<?php

namespace App\Providers;

use App\Repositories\UploadRepository;
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
    public function register(): void
    {
        // Bind the VendorRepository to VendorService
        $this->app->bind(VendorService::class, function ($app) {
            $vendorRepository = $app->make(VendorRepository::class);
            $uploadRepository = $app->make(UploadRepository::class);

            return new VendorService($vendorRepository, $uploadRepository);
        });
    }
}
