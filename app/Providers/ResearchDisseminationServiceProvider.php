<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ResearchDisseminationRepository;
use App\Services\ResearchDisseminationService;

class ResearchDisseminationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ResearchDisseminationRepository to ResearchDisseminationService
        $this->app->bind(ResearchDisseminationService::class, function ($app) {
            return new ResearchDisseminationService($app->make(ResearchDisseminationRepository::class));
        });
    }
}
