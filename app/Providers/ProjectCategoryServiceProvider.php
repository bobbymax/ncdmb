<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectCategoryRepository;
use App\Services\ProjectCategoryService;

class ProjectCategoryServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProjectCategoryRepository to ProjectCategoryService
        $this->app->bind(ProjectCategoryService::class, function ($app) {
            $projectCategoryRepository = $app->make(ProjectCategoryRepository::class);

            return new ProjectCategoryService($projectCategoryRepository);
        });
    }
}
