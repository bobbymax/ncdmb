<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\LegalReviewRepository;
use App\Services\LegalReviewService;

class LegalReviewServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the LegalReviewRepository to LegalReviewService
        $this->app->bind(LegalReviewService::class, function ($app) {
            $legalReviewRepository = $app->make(LegalReviewRepository::class);

            return new LegalReviewService($legalReviewRepository);
        });
    }
}
