<?php

namespace App\Providers;

use App\Http\Resources\SubBudgetHeadResource;
use Illuminate\Support\ServiceProvider;
use App\Repositories\SubBudgetHeadRepository;
use App\Services\SubBudgetHeadService;

class SubBudgetHeadServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the SubBudgetHeadRepository to SubBudgetHeadService
        $this->app->bind(SubBudgetHeadService::class, function ($app) {
            $subBudgetHeadRepository = $app->make(SubBudgetHeadRepository::class);
            $subBudgetHeadResource = $app->make(SubBudgetHeadResource::class);
            return new SubBudgetHeadService($subBudgetHeadRepository, $subBudgetHeadResource);
        });
    }
}
