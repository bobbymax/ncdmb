<?php

namespace App\Providers;

use App\Repositories\UploadRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\FlightReservationRepository;
use App\Services\FlightReservationService;

class FlightReservationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the FlightReservationRepository to FlightReservationService
        $this->app->bind(FlightReservationService::class, function ($app) {
            $flightReservationRepository = $app->make(FlightReservationRepository::class);
            $uploadRepository = $app->make(UploadRepository::class);

            return new FlightReservationService($flightReservationRepository, $uploadRepository);
        });
    }
}
