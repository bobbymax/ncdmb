<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\SignatureRepository;
use App\Services\SignatureService;

class SignatureServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the SignatureRepository to SignatureService
        $this->app->bind(SignatureService::class, function ($app) {
            $signatureRepository = $app->make(SignatureRepository::class);

            return new SignatureService($signatureRepository);
        });
    }
}
