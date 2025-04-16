<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\JournalRepository;
use App\Services\JournalService;

class JournalServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the JournalRepository to JournalService
        $this->app->bind(JournalService::class, function ($app) {
            $journalRepository = $app->make(JournalRepository::class);

            return new JournalService($journalRepository);
        });
    }
}
