<?php

namespace App\Providers;

use App\Http\Resources\HotelReservationResource;
use App\Repositories\ReserveRepository;
use App\Repositories\UploadRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\HotelReservationRepository;
use App\Services\HotelReservationService;

class HotelReservationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the HotelReservationRepository to HotelReservationService
        $this->app->bind(HotelReservationService::class, function ($app) {
            $hotelReservationRepository = $app->make(HotelReservationRepository::class);
            $hotelReservationResource = $app->make(HotelReservationResource::class);
            $uploadRepository = $app->make(UploadRepository::class);
            $reserveRepository = $app->make(ReserveRepository::class);

            return new HotelReservationService($hotelReservationRepository, $hotelReservationResource, $uploadRepository, $reserveRepository);
        });
    }
}
