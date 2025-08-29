<?php

namespace App\Providers;

use App\Repositories\InvoiceItemRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\InvoiceRepository;
use App\Services\InvoiceService;

class InvoiceServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the InvoiceRepository to InvoiceService
        $this->app->bind(InvoiceService::class, function ($app) {
            $invoiceRepository = $app->make(InvoiceRepository::class);
            $invoiceItemRepository = $app->make(InvoiceItemRepository::class);

            return new InvoiceService($invoiceRepository, $invoiceItemRepository);
        });
    }
}
