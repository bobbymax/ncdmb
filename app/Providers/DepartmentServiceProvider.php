<?php

namespace App\Providers;

use App\Http\Resources\DepartmentResource;
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
            $departmentResource = $app->make(DepartmentResource::class);
            return new DepartmentService($departmentRepository, $departmentResource);
        });
    }
}
