<?php

namespace App\Providers;

use App\Engine\ControlEngine;
use App\Repositories\DocumentRepository;
use App\Repositories\ExpenditureRepository;
use App\Repositories\ProgressTrackerRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\WorkflowRepository;
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
            $expenditureRepository = $app->make(ExpenditureRepository::class);
            $transactionRepository = $app->make(TransactionRepository::class);
            $documentRepository = $app->make(DocumentRepository::class);
            $workflowRepository = $app->make(WorkflowRepository::class);
            $progressTrackerRepository = $app->make(ProgressTrackerRepository::class);

            return new PaymentService(
                $paymentRepository,
                $expenditureRepository,
                $transactionRepository,
                $documentRepository,
                $workflowRepository,
                $progressTrackerRepository
            );
        });
    }
}
