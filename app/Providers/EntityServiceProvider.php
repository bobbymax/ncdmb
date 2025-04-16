<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\EntityRepository;
use App\Services\EntityService;

class EntityServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the EntityRepository to EntityService
        $this->app->bind(EntityService::class, function ($app) {
            $entityRepository = $app->make(EntityRepository::class);

            return new EntityService($entityRepository);
        });
    }
}
