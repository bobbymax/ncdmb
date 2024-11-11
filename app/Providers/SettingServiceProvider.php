<?php

namespace App\Providers;

use App\Http\Resources\SettingResource;
use Illuminate\Support\ServiceProvider;
use App\Repositories\SettingRepository;
use App\Services\SettingService;

class SettingServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the SettingRepository to SettingService
        $this->app->bind(SettingService::class, function ($app) {
            $settingRepository = $app->make(SettingRepository::class);
            $settingResource = $app->make(SettingResource::class);
            return new SettingService($settingRepository, $settingResource);
        });
    }
}
