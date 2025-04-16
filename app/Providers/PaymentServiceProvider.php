<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\PaymentRepository;
use App\Services\PaymentService;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Register services and bind the repository to the service.
     *
     * @return void
     */
    public function register(): void
    {
        // Bind the PaymentRepository to PaymentService
        $this->app->bind(PaymentService::class, function ($app) {
            $paymentRepository = $app->make(PaymentRepository::class);

            return new PaymentService($paymentRepository);
        });
    }
}
