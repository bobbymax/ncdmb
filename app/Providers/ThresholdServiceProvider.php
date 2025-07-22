<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ThresholdRepository;
use App\Services\ThresholdService;

class ThresholdServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ThresholdRepository to ThresholdService
        $this->app->bind(ThresholdService::class, function ($app) {
            $thresholdRepository = $app->make(ThresholdRepository::class);

            return new ThresholdService($thresholdRepository);
        });
    }
}
