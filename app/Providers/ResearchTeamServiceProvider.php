<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ResearchTeamRepository;
use App\Services\ResearchTeamService;

class ResearchTeamServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ResearchTeamRepository to ResearchTeamService
        $this->app->bind(ResearchTeamService::class, function ($app) {
            return new ResearchTeamService($app->make(ResearchTeamRepository::class));
        });
    }
}
