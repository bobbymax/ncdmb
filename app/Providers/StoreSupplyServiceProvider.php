<?php

namespace App\Providers;

use App\Http\Resources\StoreSupplyResource;
use Illuminate\Support\ServiceProvider;
use App\Repositories\StoreSupplyRepository;
use App\Services\StoreSupplyService;

class StoreSupplyServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the StoreSupplyRepository to StoreSupplyService
        $this->app->bind(StoreSupplyService::class, function ($app) {
            $storeSupplyRepository = $app->make(StoreSupplyRepository::class);
            $storeSupplyResource = $app->make(StoreSupplyResource::class);

            return new StoreSupplyService($storeSupplyRepository, $storeSupplyResource);
        });
    }
}
