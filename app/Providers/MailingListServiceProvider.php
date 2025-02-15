<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\MailingListRepository;
use App\Services\MailingListService;

class MailingListServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the MailingListRepository to MailingListService
        $this->app->bind(MailingListService::class, function ($app) {
            $mailingListRepository = $app->make(MailingListRepository::class);

            return new MailingListService($mailingListRepository);
        });
    }
}
