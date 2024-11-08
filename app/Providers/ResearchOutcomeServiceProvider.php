<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ResearchOutcomeRepository;
use App\Services\ResearchOutcomeService;

class ResearchOutcomeServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ResearchOutcomeRepository to ResearchOutcomeService
        $this->app->bind(ResearchOutcomeService::class, function ($app) {
            return new ResearchOutcomeService($app->make(ResearchOutcomeRepository::class));
        });
    }
}
