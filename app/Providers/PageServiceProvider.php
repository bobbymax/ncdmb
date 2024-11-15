<?php

namespace App\Providers;

use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\PageRepository;
use App\Services\PageService;

class PageServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the PageRepository to PageService
        $this->app->bind(PageService::class, function ($app) {
            $pageRepository = $app->make(PageRepository::class);
            $permissionRepository = $app->make(PermissionRepository::class);
            $roleRepository = $app->make(RoleRepository::class);
            return new PageService($pageRepository, $permissionRepository, $roleRepository);
        });
    }
}
