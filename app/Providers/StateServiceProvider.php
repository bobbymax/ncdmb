<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\StateRepository;
use App\Services\StateService;

class StateServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the StateRepository to StateService
        $this->app->bind(StateService::class, function ($app) {
            $stateRepository = $app->make(StateRepository::class);

            return new StateService($stateRepository);
        });
    }
}
