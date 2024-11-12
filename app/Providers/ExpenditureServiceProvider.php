<?php

namespace App\Providers;

use App\Http\Resources\ExpenditureResource;
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
            $expenditureResource = $app->make(ExpenditureResource::class);
            $fundRepository = $app->make(FundRepository::class);
            $claimRepository = $app->make(ClaimRepository::class);
            $projectMilestoneRepository = $app->make(ProjectMilestoneRepository::class);
            $mandateRepository = $app->make(MandateRepository::class);

            return new ExpenditureService($expenditureRepository, $expenditureResource, $fundRepository, $claimRepository, $projectMilestoneRepository, $mandateRepository);
        });
    }
}
