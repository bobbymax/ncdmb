<?php

namespace App\Providers;

use App\Engine\ControlEngine;
use App\Repositories\DocumentRepository;
use App\Repositories\ExpenseRepository;
use App\Repositories\TripRepository;
use App\Repositories\UploadRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\ClaimRepository;
use App\Services\ClaimService;

class ClaimServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ClaimRepository to ClaimService
        $this->app->bind(ClaimService::class, function ($app) {
            $claimRepository = $app->make(ClaimRepository::class);
            $expenseRepository = $app->make(ExpenseRepository::class);
            $uploadRepository = $app->make(UploadRepository::class);
            $documentRepository = $app->make(DocumentRepository::class);
            $controlEngine  = $app->make(ControlEngine::class);
            return new ClaimService($claimRepository, $expenseRepository, $uploadRepository, $documentRepository, $controlEngine);
        });
    }
}
