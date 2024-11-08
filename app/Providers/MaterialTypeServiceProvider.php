<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\MaterialTypeRepository;
use App\Services\MaterialTypeService;

class MaterialTypeServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the MaterialTypeRepository to MaterialTypeService
        $this->app->bind(MaterialTypeService::class, function ($app) {
            return new MaterialTypeService($app->make(MaterialTypeRepository::class));
        });
    }
}
