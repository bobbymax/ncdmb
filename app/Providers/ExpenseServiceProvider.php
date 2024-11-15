<?php

namespace App\Providers;


use Illuminate\Support\ServiceProvider;
use App\Repositories\ExpenseRepository;
use App\Services\ExpenseService;

class ExpenseServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the ExpenseRepository to ExpenseService
        $this->app->bind(ExpenseService::class, function ($app) {
            $expenseRepository = $app->make(ExpenseRepository::class);
            return new ExpenseService($expenseRepository);
        });
    }
}
