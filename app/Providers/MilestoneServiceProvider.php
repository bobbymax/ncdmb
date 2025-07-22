<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\MilestoneRepository;
use App\Services\MilestoneService;

class MilestoneServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the MilestoneRepository to MilestoneService
        $this->app->bind(MilestoneService::class, function ($app) {
            $milestoneRepository = $app->make(MilestoneRepository::class);

            return new MilestoneService($milestoneRepository);
        });
    }
}
