<?php

namespace App\Providers;

use App\Http\Resources\RoomResource;
use Illuminate\Support\ServiceProvider;
use App\Repositories\RoomRepository;
use App\Services\RoomService;

class RoomServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the RoomRepository to RoomService
        $this->app->bind(RoomService::class, function ($app) {
            $roomRepository = $app->make(RoomRepository::class);
            $roomResource = $app->make(RoomResource::class);

            return new RoomService($roomRepository, $roomResource);
        });
    }
}
