<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ResourceEditorRepository;
use App\Services\ResourceEditorService;

class ResourceEditorServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ResourceEditorRepository to ResourceEditorService
        $this->app->bind(ResourceEditorService::class, function ($app) {
            $resourceEditorRepository = $app->make(ResourceEditorRepository::class);

            return new ResourceEditorService($resourceEditorRepository);
        });
    }
}
