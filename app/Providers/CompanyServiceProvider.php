<?php

namespace App\Providers;

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
    public function register()
    {
        // Bind the CompanyRepository to CompanyService
        $this->app->bind(CompanyService::class, function ($app) {
            return new CompanyService($app->make(CompanyRepository::class));
        });
    }
}
