<?php

namespace App\Services;

use App\Repositories\ExpenditureRepository;
use App\Repositories\FundRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\DB;

class TransactionService extends BaseService
{
    protected ExpenditureRepository $expenditureRepository;
    protected FundRepository $fundRepository;
    public function __construct(
        TransactionRepository $transactionRepository,
        ExpenditureRepository $expenditureRepository,
        FundRepository $fundRepository
    ) {
        parent::__construct($transactionRepository);
        $this->expenditureRepository = $expenditureRepository;
        $this->fundRepository = $fundRepository;
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

    public function store(array $data)
    {
        return  DB::transaction(function () use ($data) {
            $transaction = parent::store($data);

            $expenditure = $this->expenditureRepository->find($transaction->expenditure_id);
            $fund = $this->fundRepository->find($transaction->fund_id);

            if ($transaction->stage === 'audit') {
                $expenditure->update([
                    'stage' => $transaction->stage,
                    'status' => "paid",
                ]);

                $fund->total_actual_spent_amount += $transaction->transaction_amount;
                $fund->total_actual_balance -= $transaction->transaction_amount;
                $fund->save();
            }

            return $transaction;
        });
    }
}
