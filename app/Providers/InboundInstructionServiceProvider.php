<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\InboundInstructionRepository;
use App\Services\InboundInstructionService;

class InboundInstructionServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the InboundInstructionRepository to InboundInstructionService
        $this->app->bind(InboundInstructionService::class, function ($app) {
            $inboundInstructionRepository = $app->make(InboundInstructionRepository::class);

            return new InboundInstructionService($inboundInstructionRepository);
        });
    }
}
