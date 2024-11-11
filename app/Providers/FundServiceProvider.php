<?php

namespace App\Providers;

use App\Http\Resources\FundResource;
use Illuminate\Support\ServiceProvider;
use App\Repositories\FundRepository;
use App\Services\FundService;

class FundServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the FundRepository to FundService
        $this->app->bind(FundService::class, function ($app) {
            $fundRepository = $app->make(FundRepository::class);
            $fundResource = $app->make(FundResource::class);
            return new FundService($fundRepository, $fundResource);
        });
    }
}
