<?php

namespace App\Providers;


use App\Repositories\GradeLevelRepository;
use App\Repositories\RemunerationRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\AllowanceRepository;
use App\Services\AllowanceService;

class AllowanceServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the AllowanceRepository to AllowanceService
        $this->app->bind(AllowanceService::class, function ($app) {
            $allowanceRepository = $app->make(AllowanceRepository::class);
            $remunerationRepository = $app->make(RemunerationRepository::class);
            $gradeLevelRepository = $app->make(GradeLevelRepository::class);

            return new AllowanceService(
                $allowanceRepository,
                $remunerationRepository,
                $gradeLevelRepository
            );
        });
    }
}
