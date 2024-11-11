<?php

namespace App\Services;

use App\Repositories\TransactionRepository;

class TransactionService extends BaseService
{
    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->repository = $transactionRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'fund_id' => 'required|integer|exists:funds,id',
            'department_id' => 'required|integer|exists:departments,id',
            'expenditure_id' => 'required|integer|exists:expenditures,id',
            'payment_batch_id' => 'required|integer|exists:payment_batches,id',
            'staff_id' => 'sometimes|integer|min:0',
            'vendor_id' => 'sometimes|integer|min:0',
            'currency' => 'required|string|in:NGN,USD,EUR,GBP',
            'transaction_amount' => 'required|numeric|min:1',
            'type' => 'required|string|in:debit,credit',
            'transaction_date' => 'required|date',
            'date_posted' => 'nullable|date|after_or_equal:transaction_date',
            'budget_year' => 'required|integer|digits:4',
            'query_note' => 'nullable|string|min:5',
            'query_response' => 'nullable|string|min:5',
            'stage' => 'required|string|in:budget-office,treasury,audit',
            'status' => 'required|string|in:in-progress,queried,cleared,altered'
        ];
    }
}
