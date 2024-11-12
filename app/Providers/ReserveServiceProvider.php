<?php

namespace App\Providers;

use App\Http\Resources\ReserveResource;
use App\Repositories\ExpenditureRepository;
use App\Repositories\FundRepository;
use App\Repositories\UploadRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\ReserveRepository;
use App\Services\ReserveService;

class ReserveServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ReserveRepository to ReserveService
        $this->app->bind(ReserveService::class, function ($app) {
            $reserveRepository = $app->make(ReserveRepository::class);
            $reserveResource = $app->make(ReserveResource::class);
            $uploadRepository = $app->make(UploadRepository::class);
            $fundRepository = $app->make(FundRepository::class);
            $expenditureRepository = $app->make(ExpenditureRepository::class);

            return new ReserveService($reserveRepository, $reserveResource, $uploadRepository, $fundRepository, $expenditureRepository);
        });
    }
}
