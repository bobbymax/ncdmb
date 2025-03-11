<?php

namespace App\Providers;

use App\Repositories\ClaimRepository;
use App\Repositories\FundRepository;
use App\Repositories\MandateRepository;
use App\Repositories\ProjectMilestoneRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\ExpenditureRepository;
use App\Services\ExpenditureService;

class ExpenditureServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ExpenditureRepository to ExpenditureService
        $this->app->bind(ExpenditureService::class, function ($app) {
            $expenditureRepository = $app->make(ExpenditureRepository::class);

            return new ExpenditureService($expenditureRepository);
        });
    }
}
