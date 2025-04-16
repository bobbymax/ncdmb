<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\JournalEntryRepository;
use App\Services\JournalEntryService;

class JournalEntryServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the JournalEntryRepository to JournalEntryService
        $this->app->bind(JournalEntryService::class, function ($app) {
            $journalEntryRepository = $app->make(JournalEntryRepository::class);

            return new JournalEntryService($journalEntryRepository);
        });
    }
}
