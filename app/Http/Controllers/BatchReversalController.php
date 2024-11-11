<?php

namespace App\Http\Controllers;

use App\Services\BatchReversalService;

class BatchReversalController extends Controller
{
    public function __construct(BatchReversalService $batchReversalService) {
        $this->service = $batchReversalService;
        $this->name = 'BatchReversal';
    }
}
