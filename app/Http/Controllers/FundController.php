<?php

namespace App\Http\Controllers;

use App\Http\Resources\FundResource;
use App\Services\FundService;

class FundController extends BaseController
{
    public function __construct(FundService $fundService) {
        parent::__construct($fundService, 'Fund', FundResource::class);
    }

    public function totalCurrentCommittment(int $fund): \Illuminate\Http\JsonResponse
    {
        $fundObject = $this->service->show($fund);

        if (!$fundObject) {
            return $this->error(null, 'This record was not found', 422);
        }

        $total_commitment = $fundObject->expenditures->where('status', '!=', 'reversed')->sum('amount');

        return $this->success($total_commitment);
    }
}
