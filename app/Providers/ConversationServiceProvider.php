<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\ConversationRepository;
use App\Services\ConversationService;

class ConversationServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ConversationRepository to ConversationService
        $this->app->bind(ConversationService::class, function ($app) {
            $conversationRepository = $app->make(ConversationRepository::class);

            return new ConversationService($conversationRepository);
        });
    }
}
