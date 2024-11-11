<?php

namespace App\Providers;

use App\Http\Resources\PaymentBatchResource;
use App\Repositories\ExpenditureRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\PaymentBatchRepository;
use App\Services\PaymentBatchService;

class PaymentBatchServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the PaymentBatchRepository to PaymentBatchService
        $this->app->bind(PaymentBatchService::class, function ($app) {
            $paymentBatchRepository = $app->make(PaymentBatchRepository::class);
            $paymentBatchResource = $app->make(PaymentBatchResource::class);
            $expenditureRepository = $app->make(ExpenditureRepository::class);

            return new PaymentBatchService($paymentBatchRepository, $paymentBatchResource, $expenditureRepository);
        });
    }
}
