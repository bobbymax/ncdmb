<?php

namespace App\Providers;

use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\CompanyRepresentativeRepository;
use App\Services\CompanyRepresentativeService;

class CompanyRepresentativeServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the CompanyRepresentativeRepository to CompanyRepresentativeService
        $this->app->bind(CompanyRepresentativeService::class, function ($app) {
            $companyRepresentativeRepository = $app->make(CompanyRepresentativeRepository::class);

            return new CompanyRepresentativeService($companyRepresentativeRepository);
        });
    }
}
