<?php

namespace App\Http\Controllers;

use App\Http\Resources\BatchReversalResource;
use App\Services\BatchReversalService;

class BatchReversalController extends BaseController
{
    public function __construct(BatchReversalService $batchReversalService) {
        parent::__construct($batchReversalService, 'BatchReversal', BatchReversalResource::class);
    }
}
