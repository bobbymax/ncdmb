<?php

namespace App\Http\Controllers;

use App\Services\BatchReversalService;

class BatchReversalController extends Controller
{
    public function __construct(BatchReversalService $batchReversalService) {
        parent::__construct($batchReversalService, 'BatchReversal');
    }
}
