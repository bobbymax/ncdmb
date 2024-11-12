<?php

namespace App\Providers;

use App\Http\Resources\FlightItineraryResource;
use App\Repositories\FundRepository;
use App\Repositories\ReserveRepository;
use App\Repositories\UploadRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\FlightItineraryRepository;
use App\Services\FlightItineraryService;

class FlightItineraryServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the FlightItineraryRepository to FlightItineraryService
        $this->app->bind(FlightItineraryService::class, function ($app) {
            $flightItineraryRepository = $app->make(FlightItineraryRepository::class);
            $flightItineraryResource = $app->make(FlightItineraryResource::class);
            $uploadRepository = $app->make(UploadRepository::class);
            $reserveRepository = $app->make(ReserveRepository::class);

            return new FlightItineraryService($flightItineraryRepository, $flightItineraryResource, $uploadRepository, $reserveRepository);
        });
    }
}
