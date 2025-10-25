<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\QueryRepository;
use App\Services\QueryService;

class QueryServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the QueryRepository to QueryService
        $this->app->bind(QueryService::class, function ($app) {
            $queryRepository = $app->make(QueryRepository::class);

            return new QueryService($queryRepository);
        });
    }
}
