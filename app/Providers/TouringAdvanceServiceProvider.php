<?php

namespace App\Providers;


use App\Repositories\ClaimRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\TouringAdvanceRepository;
use App\Services\TouringAdvanceService;

class TouringAdvanceServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the TouringAdvanceRepository to TouringAdvanceService
        $this->app->bind(TouringAdvanceService::class, function ($app) {
            $touringAdvanceRepository = $app->make(TouringAdvanceRepository::class);
            $claimRepository = $app->make(ClaimRepository::class);
            return new TouringAdvanceService($touringAdvanceRepository, $claimRepository);
        });
    }
}
