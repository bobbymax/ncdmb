<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\GroupRepository;
use App\Services\GroupService;

class GroupServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the GroupRepository to GroupService
        $this->app->bind(GroupService::class, function ($app) {
            $groupRepository = $app->make(GroupRepository::class);

            return new GroupService($groupRepository);
        });
    }
}
