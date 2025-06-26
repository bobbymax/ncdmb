<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\BlockRepository;
use App\Services\BlockService;

class BlockServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the BlockRepository to BlockService
        $this->app->bind(BlockService::class, function ($app) {
            $blockRepository = $app->make(BlockRepository::class);

            return new BlockService($blockRepository);
        });
    }
}
