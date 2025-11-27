<?php

namespace App\Providers;

use App\Repositories\CompanyRepresentativeRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\CompanyRepository;
use App\Services\CompanyService;

class CompanyServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the CompanyRepository to CompanyService
        $this->app->bind(CompanyService::class, function ($app) {
            $companyRepository = $app->make(CompanyRepository::class);
            $companyRepresentativeRepository = $app->make(CompanyRepresentativeRepository::class);

            return new CompanyService($companyRepository, $companyRepresentativeRepository);
        });
    }
}
