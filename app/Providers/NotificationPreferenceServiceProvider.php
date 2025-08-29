<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\NotificationPreferenceRepository;
use App\Services\NotificationPreferenceService;

class NotificationPreferenceServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the NotificationPreferenceRepository to NotificationPreferenceService
        $this->app->bind(NotificationPreferenceService::class, function ($app) {
            $notificationPreferenceRepository = $app->make(NotificationPreferenceRepository::class);

            return new NotificationPreferenceService($notificationPreferenceRepository);
        });
    }
}
