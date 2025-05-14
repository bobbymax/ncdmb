<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\JournalTypeRepository;
use App\Services\JournalTypeService;

class JournalTypeServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the JournalTypeRepository to JournalTypeService
        $this->app->bind(JournalTypeService::class, function ($app) {
            $journalTypeRepository = $app->make(JournalTypeRepository::class);

            return new JournalTypeService($journalTypeRepository);
        });
    }
}
