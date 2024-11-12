<?php

namespace App\Providers;

use App\Http\Resources\HotelResource;
use App\Repositories\GradeLevelRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\HotelRepository;
use App\Services\HotelService;

class HotelServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the HotelRepository to HotelService
        $this->app->bind(HotelService::class, function ($app) {
            $hotelRepository = $app->make(HotelRepository::class);
            $hotelResource = $app->make(HotelResource::class);
            $gradeLevelRepository = $app->make(GradeLevelRepository::class);

            return new HotelService($hotelRepository, $hotelResource, $gradeLevelRepository);
        });
    }
}
