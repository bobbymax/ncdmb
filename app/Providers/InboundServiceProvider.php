<?php

namespace App\Providers;

use App\Repositories\UploadRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\InboundRepository;
use App\Services\InboundService;

class InboundServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the InboundRepository to InboundService
        $this->app->bind(InboundService::class, function ($app) {
            $inboundRepository = $app->make(InboundRepository::class);
            $uploadRepository = $app->make(UploadRepository::class);

            return new InboundService($inboundRepository, $uploadRepository);
        });
    }
}
