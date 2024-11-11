<?php

namespace App\Providers;

use App\Http\Resources\TouringAdvanceResource;
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
            $touringAdvanceResource = $app->make(TouringAdvanceResource::class);
            $claimRepository = $app->make(ClaimRepository::class);
            return new TouringAdvanceService($touringAdvanceRepository, $touringAdvanceResource, $claimRepository);
        });
    }
}
