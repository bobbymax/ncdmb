<?php

namespace App\Providers;

use App\Repositories\GroupRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\DocumentPanelRepository;
use App\Services\DocumentPanelService;

class DocumentPanelServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the DocumentPanelRepository to DocumentPanelService
        $this->app->bind(DocumentPanelService::class, function ($app) {
            $documentPanelRepository = $app->make(DocumentPanelRepository::class);
            $groupRepository = $app->make(GroupRepository::class);

            return new DocumentPanelService($documentPanelRepository, $groupRepository);
        });
    }
}
