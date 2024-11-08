<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ResearchTeamDevelopmentRepository;
use App\Services\ResearchTeamDevelopmentService;

class ResearchTeamDevelopmentServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ResearchTeamDevelopmentRepository to ResearchTeamDevelopmentService
        $this->app->bind(ResearchTeamDevelopmentService::class, function ($app) {
            return new ResearchTeamDevelopmentService($app->make(ResearchTeamDevelopmentRepository::class));
        });
    }
}
