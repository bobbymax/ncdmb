<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectBidEvaluationRepository;
use App\Services\ProjectBidEvaluationService;

class ProjectBidEvaluationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProjectBidEvaluationRepository to ProjectBidEvaluationService
        $this->app->bind(ProjectBidEvaluationService::class, function ($app) {
            $projectBidEvaluationRepository = $app->make(ProjectBidEvaluationRepository::class);

            return new ProjectBidEvaluationService($projectBidEvaluationRepository);
        });
    }
}
