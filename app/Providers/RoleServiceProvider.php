<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\RoleRepository;
use App\Services\RoleService;

class RoleServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the RoleRepository to RoleService
        $this->app->bind(RoleService::class, function ($app) {
            return new RoleService($app->make(RoleRepository::class));
        });
    }
}
