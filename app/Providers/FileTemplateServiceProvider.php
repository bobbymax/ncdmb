<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\FileTemplateRepository;
use App\Services\FileTemplateService;

class FileTemplateServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the FileTemplateRepository to FileTemplateService
        $this->app->bind(FileTemplateService::class, function ($app) {
            $fileTemplateRepository = $app->make(FileTemplateRepository::class);

            return new FileTemplateService($fileTemplateRepository);
        });
    }
}
