<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\DisseminationChannelRepository;
use App\Services\DisseminationChannelService;

class DisseminationChannelServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register()
    {
        // Bind the DisseminationChannelRepository to DisseminationChannelService
        $this->app->bind(DisseminationChannelService::class, function ($app) {
            return new DisseminationChannelService($app->make(DisseminationChannelRepository::class));
        });
    }
}
