<?php

namespace App\Services;

use App\Http\Resources\BatchReversalResource;
use App\Repositories\BatchReversalRepository;

class BatchReversalService extends BaseService
{
    public function __construct(BatchReversalRepository $batchReversalRepository, BatchReversalResource $batchReversalResource)
    {
        parent::__construct($batchReversalRepository, $batchReversalResource);
    }

    public function rules($action = "store"): array
    {
        return [
            'payment_batch_id' => 'required|integer|exists:payment_batches,id',
            'reason_for_reversal' => 'required|string|min:5',
        ];
    }
}
