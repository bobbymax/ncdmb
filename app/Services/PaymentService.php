<?php

namespace App\Services;

use App\Repositories\PaymentRepository;

class PaymentService extends BaseService
{
    public function __construct(PaymentRepository $paymentRepository)
    {
        parent::__construct($paymentRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'payment_batch_id' => 'required|integer|exists:payment_batches,id',
            'expenditure_id' => 'required|integer|exists:expenditures,id',
            'department_id' => 'required|integer|exists:departments,id',
            'user_id' => 'required|integer|exists:users,id',
            'narration' => 'required|string|min:3',
            'total_amount_payable' => 'required|numeric|min:0',
            'payable_id' => 'required|integer',
            'payable_type' => 'required|string',
            'payment_method' => 'required|string|bank-transfer,cheque,cash,cheque-number',
            'payment_type' => 'required|string|staff,third-party',
            'currency' => 'required|string|NGN,USD,GBP,EUR,YEN',
            'period' => 'required|date',
            'fiscal_year' => 'required|integer|digits:4',
            'paid_at' => 'nullable|date',
            'status' => 'required|string|in:draft,posted,reversed',
            'entries' => 'required|array',
            'entries.*.amount' => 'required|numeric|min:0',
            'entries.*.chart_of_account_id' => 'required|integer|exists:chart_of_accounts,id',
            'entries.*.description' => 'required|string|min:3',
            'entries.*.ledger' => 'required|in:A,B,C,D,E,F,G,H,I,J,K',
            'entries.*.is_postable' => 'required|integer|in:0,1',
        ];
    }
}
