<?php

namespace App\Repositories;

use App\Handlers\CodeGenerationErrorException;
use App\Models\PaymentBatch;
use Illuminate\Support\Facades\Auth;

class PaymentBatchRepository extends BaseRepository
{
    public function __construct(PaymentBatch $paymentBatch) {
        parent::__construct($paymentBatch);
    }

    /**
     * @throws CodeGenerationErrorException
     */
    public function parse(array $data): array
    {
        return $data;
    }
}
