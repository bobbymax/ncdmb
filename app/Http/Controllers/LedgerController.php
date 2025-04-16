<?php

namespace App\Http\Controllers;


use App\Http\Resources\LedgerResource;
use App\Models\Ledger;
use App\Services\LedgerService;
use Illuminate\Support\Facades\Auth;

class LedgerController extends BaseController
{
    public function __construct(LedgerService $ledgerService) {
        parent::__construct($ledgerService, 'Ledger', LedgerResource::class);
    }

    public function getLedgers(): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $groupIds = $user->groups->pluck('id')->toArray();
        $ledgers = Ledger::with('groups')
            ->whereHas('groups', function ($query) use ($groupIds) {
                $query->whereIn('groups.id', $groupIds);
        })->get();

        return $this->success($this->jsonResource::collection($ledgers));
    }
}
