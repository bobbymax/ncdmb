<?php

namespace App\Providers;

use App\Http\Resources\BoardProjectResource;
use Illuminate\Support\ServiceProvider;
use App\Repositories\BoardProjectRepository;
use App\Services\BoardProjectService;

class BoardProjectServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the BoardProjectRepository to BoardProjectService
        $this->app->bind(BoardProjectService::class, function ($app) {
            $boardProjectRepository = $app->make(BoardProjectRepository::class);
            $boardProjectResource = $app->make(BoardProjectResource::class);
            return new BoardProjectService($boardProjectRepository, $boardProjectResource);
        });
    }
}
