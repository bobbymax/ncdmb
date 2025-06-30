<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\TemplateRepository;
use App\Services\TemplateService;

class TemplateServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the TemplateRepository to TemplateService
        $this->app->bind(TemplateService::class, function ($app) {
            $templateRepository = $app->make(TemplateRepository::class);

            return new TemplateService($templateRepository);
        });
    }
}
