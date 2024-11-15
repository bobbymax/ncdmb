<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\GradeLevelRepository;
use App\Services\GradeLevelService;

class GradeLevelServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the GradeLevelRepository to GradeLevelService
        $this->app->bind(GradeLevelService::class, function ($app) {
            $gradeLevelRepository = $app->make(GradeLevelRepository::class);
            return new GradeLevelService($gradeLevelRepository);
        });
    }
}
