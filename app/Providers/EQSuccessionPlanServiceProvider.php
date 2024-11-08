<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\EQSuccessionPlanRepository;
use App\Services\EQSuccessionPlanService;

class EQSuccessionPlanServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the EQSuccessionPlanRepository to EQSuccessionPlanService
        $this->app->bind(EQSuccessionPlanService::class, function ($app) {
            return new EQSuccessionPlanService($app->make(EQSuccessionPlanRepository::class));
        });
    }
}
