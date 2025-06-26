<?php

namespace App\Providers;

use App\Engine\ControlEngine;
use App\Repositories\DocumentRepository;
use App\Repositories\JournalEntryRepository;
use App\Repositories\TransactionRepository;
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
            $documentRepository = $app->make(DocumentRepository::class);
            $journalEntryRepository = $app->make(JournalEntryRepository::class);
            $transactionRepository = $app->make(TransactionRepository::class);
            $controlEngine  = $app->make(ControlEngine::class);

            return new JournalService($journalRepository, $documentRepository, $journalEntryRepository, $transactionRepository, $controlEngine);
        });
    }
}
