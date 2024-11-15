<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\DepartmentRepository;
use App\Services\DepartmentService;

class DepartmentServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the DepartmentRepository to DepartmentService
        $this->app->bind(DepartmentService::class, function ($app) {
            $departmentRepository = $app->make(DepartmentRepository::class);
            return new DepartmentService($departmentRepository);
        });
    }
}
