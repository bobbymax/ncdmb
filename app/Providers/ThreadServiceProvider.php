<?php

namespace App\Providers;

use App\Repositories\ConversationRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\ThreadRepository;
use App\Services\ThreadService;

class ThreadServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ThreadRepository to ThreadService
        $this->app->bind(ThreadService::class, function ($app) {
            $threadRepository = $app->make(ThreadRepository::class);
            $conversationRepository = $app->make(ConversationRepository::class);

            return new ThreadService($threadRepository, $conversationRepository);
        });
    }
}
