<?php

namespace App\Http\Controllers;

use App\Http\Resources\ClaimResource;
use App\Services\ClaimService;
use Illuminate\Http\Request;

class ClaimController extends BaseController
{
    public function __construct(ClaimService $claimService) {
        parent::__construct($claimService, 'Claim', ClaimResource::class);
    }

//    public function alter(Request $request, int $claimId): ?\Illuminate\Http\JsonResponse
//    {
//        try {
//            $claim = $this->service->manipulate($claimId, $request->except('id'));
//            return $this->success(new $this->jsonResource($claim), $this->updated($this->name));
//        } catch (\Exception $e) {
//            return $this->error(null, $e->getMessage(), 422);
//        }
//    }
}
