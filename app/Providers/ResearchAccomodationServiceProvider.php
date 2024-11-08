<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ResearchAccomodationRepository;
use App\Services\ResearchAccomodationService;

class ResearchAccomodationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ResearchAccomodationRepository to ResearchAccomodationService
        $this->app->bind(ResearchAccomodationService::class, function ($app) {
            return new ResearchAccomodationService($app->make(ResearchAccomodationRepository::class));
        });
    }
}
