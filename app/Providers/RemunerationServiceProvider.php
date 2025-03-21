<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\RemunerationRepository;
use App\Services\RemunerationService;

class RemunerationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the RemunerationRepository to RemunerationService
        $this->app->bind(RemunerationService::class, function ($app) {
            $remunerationRepository = $app->make(RemunerationRepository::class);
            return new RemunerationService($remunerationRepository);
        });
    }
}
