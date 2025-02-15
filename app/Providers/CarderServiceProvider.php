<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\CarderRepository;
use App\Services\CarderService;

class CarderServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the CarderRepository to CarderService
        $this->app->bind(CarderService::class, function ($app) {
            $carderRepository = $app->make(CarderRepository::class);

            return new CarderService($carderRepository);
        });
    }
}
