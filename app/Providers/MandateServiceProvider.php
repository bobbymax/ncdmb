<?php

namespace App\Providers;

use App\Repositories\FlightItineraryRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\MandateRepository;
use App\Services\MandateService;

class MandateServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the MandateRepository to MandateService
        $this->app->bind(MandateService::class, function ($app) {
            $mandateRepository = $app->make(MandateRepository::class);
            $flightItineraryRepository = $app->make(FlightItineraryRepository::class);

            return new MandateService($mandateRepository, $flightItineraryRepository);
        });
    }
}
