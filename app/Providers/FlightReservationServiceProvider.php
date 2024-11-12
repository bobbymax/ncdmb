<?php

namespace App\Providers;

use App\Http\Resources\FlightReservationResource;
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
            $flightReservationResource = $app->make(FlightReservationResource::class);
            $uploadRepository = $app->make(UploadRepository::class);

            return new FlightReservationService($flightReservationRepository, $flightReservationResource, $uploadRepository);
        });
    }
}
