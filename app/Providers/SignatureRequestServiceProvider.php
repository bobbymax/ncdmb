<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\SignatureRequestRepository;
use App\Services\SignatureRequestService;

class SignatureRequestServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the SignatureRequestRepository to SignatureRequestService
        $this->app->bind(SignatureRequestService::class, function ($app) {
            $signatureRequestRepository = $app->make(SignatureRequestRepository::class);

            return new SignatureRequestService($signatureRequestRepository);
        });
    }
}
