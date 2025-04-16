<?php

namespace App\Repositories;

use App\Models\Payment;
use Carbon\Carbon;

class PaymentRepository extends BaseRepository
{
    public function __construct(Payment $payment) {
        parent::__construct($payment);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'period' => Carbon::parse($data['period']),
            'paid_at' => isset($data['paid_at']) ? Carbon::parse($data['paid_at']) : null,
        ];
    }
}
