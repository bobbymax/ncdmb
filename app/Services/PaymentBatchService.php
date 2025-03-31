<?php

namespace App\Services;

use App\Repositories\ExpenditureRepository;
use App\Repositories\PaymentBatchRepository;
use Illuminate\Support\Facades\DB;

class PaymentBatchService extends BaseService
{
    protected ExpenditureRepository $expenditureRepository;
    public function __construct(
        PaymentBatchRepository $paymentBatchRepository,
        ExpenditureRepository $expenditureRepository
    ) {
        parent::__construct($paymentBatchRepository);
        $this->expenditureRepository = $expenditureRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'fund_id' => 'required|integer|exists:funds,id',
            'budget_year' => 'required|integer|digits:4',
            'type' => 'required|string|in:staff,third-party',
            'document_drafts' => 'required|array',
            'document_drafts.*.id' => 'required|integer|exists:document_drafts,id',
        ];
    }

    public function store(array $data)
    {
        return  DB::transaction(function () use ($data) {
            $batch = parent::store($data);

            foreach ($data['expenditures'] as $value) {
                $expenditure = $this->expenditureRepository->find($value['id']);

                $expenditure->update([
                    'batch_id' => $batch->id,
                    'stage' => 'batched',
                    'status' => 'cleared'
                ]);

                // Send out Email
                // Send Out Event
            }

            $batch->update([
                'status' => 'dispatched'
            ]);

            return $batch;
        });
    }
}
