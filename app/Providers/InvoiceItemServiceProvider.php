<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\InvoiceItemRepository;
use App\Services\InvoiceItemService;

class InvoiceItemServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the InvoiceItemRepository to InvoiceItemService
        $this->app->bind(InvoiceItemService::class, function ($app) {
            $invoiceItemRepository = $app->make(InvoiceItemRepository::class);

            return new InvoiceItemService($invoiceItemRepository);
        });
    }
}
