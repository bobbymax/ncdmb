<?php

namespace App\Providers;

use App\Repositories\GroupRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\WidgetRepository;
use App\Services\WidgetService;

class WidgetServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the WidgetRepository to WidgetService
        $this->app->bind(WidgetService::class, function ($app) {
            $widgetRepository = $app->make(WidgetRepository::class);
            $groupRepository = $app->make(GroupRepository::class);

            return new WidgetService($widgetRepository, $groupRepository);
        });
    }
}
