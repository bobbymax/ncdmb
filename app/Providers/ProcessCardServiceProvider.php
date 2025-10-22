<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProcessCardRepository;
use App\Services\ProcessCardService;

class ProcessCardServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProcessCardRepository to ProcessCardService
        $this->app->bind(ProcessCardService::class, function ($app) {
            $processCardRepository = $app->make(ProcessCardRepository::class);

            return new ProcessCardService($processCardRepository);
        });
    }
}
