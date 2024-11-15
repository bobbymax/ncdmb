<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\UserRepository;
use App\Services\UserService;

class UserServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the UserRepository to UserService
        $this->app->bind(UserService::class, function ($app) {
            $userRepository = $app->make(UserRepository::class);
            return new UserService($userRepository);
        });
    }
}
