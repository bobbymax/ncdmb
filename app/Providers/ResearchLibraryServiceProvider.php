<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ResearchLibraryRepository;
use App\Services\ResearchLibraryService;

class ResearchLibraryServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ResearchLibraryRepository to ResearchLibraryService
        $this->app->bind(ResearchLibraryService::class, function ($app) {
            return new ResearchLibraryService($app->make(ResearchLibraryRepository::class));
        });
    }
}
