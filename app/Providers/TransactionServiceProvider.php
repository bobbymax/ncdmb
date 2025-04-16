<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\TransactionRepository;
use App\Services\TransactionService;

class TransactionServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the TransactionRepository to TransactionService
        $this->app->bind(TransactionService::class, function ($app) {
            $transactionRepository = $app->make(TransactionRepository::class);

            return new TransactionService($transactionRepository);
        });
    }
}
