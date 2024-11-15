<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use App\Repositories\PermissionRepository;
use App\Services\PermissionService;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the PermissionRepository to PermissionService
        $this->app->bind(PermissionService::class, function ($app) {
            $permissionRepository = $app->make(PermissionRepository::class);
            return new PermissionService($permissionRepository);
        });
    }
}
