<?php

namespace App\Providers;

use App\Http\Resources\UploadResource;
use App\Repositories\UserRepository;
use App\Repositories\VendorRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\UploadRepository;
use App\Services\UploadService;

class UploadServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the UploadRepository to UploadService
        $this->app->bind(UploadService::class, function ($app) {
            $uploadRepository = $app->make(UploadRepository::class);
            $uploadResource = $app->make(UploadResource::class);
            return new UploadService($uploadRepository, $uploadResource);
        });
    }
}
