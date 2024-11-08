<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ResearchCentreRepository;
use App\Services\ResearchCentreService;

class ResearchCentreServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ResearchCentreRepository to ResearchCentreService
        $this->app->bind(ResearchCentreService::class, function ($app) {
            return new ResearchCentreService($app->make(ResearchCentreRepository::class));
        });
    }
}
