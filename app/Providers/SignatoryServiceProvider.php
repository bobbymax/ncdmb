<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\SignatoryRepository;
use App\Services\SignatoryService;

class SignatoryServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the SignatoryRepository to SignatoryService
        $this->app->bind(SignatoryService::class, function ($app) {
            $signatoryRepository = $app->make(SignatoryRepository::class);

            return new SignatoryService($signatoryRepository);
        });
    }
}
