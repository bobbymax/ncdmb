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
        $prefix = $data['type'] === 'third-party-payment' ? 'TPP' : 'SP';
        unset($data['expenditures']);

        return [
            ...$data,
            'user_id' => Auth::user()->id,
            'department_id' => Auth::user()->department_id,
            'code' => $data['code'] ?? $this->generate('code', $prefix),
            'total_approved_payable_amount' => $data['total_payable_amount'],
        ];
    }
}
