<?php

namespace App\Services;

use App\Repositories\BatchReversalRepository;

class BatchReversalService extends BaseService
{
    public function __construct(BatchReversalRepository $batchReversalRepository)
    {
        $this->repository = $batchReversalRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'payment_batch_id' => 'required|integer|exists:payment_batches,id',
            'reason_for_reversal' => 'required|string|min:5',
        ];
    }
}
