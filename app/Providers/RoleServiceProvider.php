<?php

namespace App\Providers;

use App\Http\Resources\RoleResource;
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
    public function register(): void
    {
        // Bind the RoleRepository to RoleService
        $this->app->bind(RoleService::class, function ($app) {
            $roleRepository = $app->make(RoleRepository::class);
            $roleResource = $app->make(RoleResource::class);
            return new RoleService($roleRepository, $roleResource);
        });
    }
}