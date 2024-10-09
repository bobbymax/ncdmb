<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ReviewRepository;
use App\Services\ReviewService;

class ReviewServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the ReviewRepository to ReviewService
        $this->app->bind(ReviewService::class, function ($app) {
            return new ReviewService($app->make(ReviewRepository::class));
        });
    }
}
