<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProcuredMaterialRepository;
use App\Services\ProcuredMaterialService;

class ProcuredMaterialServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ProcuredMaterialRepository to ProcuredMaterialService
        $this->app->bind(ProcuredMaterialService::class, function ($app) {
            return new ProcuredMaterialService($app->make(ProcuredMaterialRepository::class));
        });
    }
}
