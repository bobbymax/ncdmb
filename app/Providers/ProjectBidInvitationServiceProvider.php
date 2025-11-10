<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ProjectBidInvitationRepository;
use App\Services\ProjectBidInvitationService;

class ProjectBidInvitationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ProjectBidInvitationRepository to ProjectBidInvitationService
        $this->app->bind(ProjectBidInvitationService::class, function ($app) {
            $projectBidInvitationRepository = $app->make(ProjectBidInvitationRepository::class);

            return new ProjectBidInvitationService($projectBidInvitationRepository);
        });
    }
}
