<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectEvaluationCommitteeRepository;
use App\Services\ProjectEvaluationCommitteeService;

class ProjectEvaluationCommitteeServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProjectEvaluationCommitteeRepository to ProjectEvaluationCommitteeService
        $this->app->bind(ProjectEvaluationCommitteeService::class, function ($app) {
            $projectEvaluationCommitteeRepository = $app->make(ProjectEvaluationCommitteeRepository::class);

            return new ProjectEvaluationCommitteeService($projectEvaluationCommitteeRepository);
        });
    }
}
