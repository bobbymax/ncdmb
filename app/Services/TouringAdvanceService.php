<?php

namespace App\Services;

use App\Repositories\ClaimRepository;
use App\Repositories\TouringAdvanceRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TouringAdvanceService extends BaseService
{
    protected ClaimRepository $claimRepository;
    public function __construct(TouringAdvanceRepository $touringAdvanceRepository, ClaimRepository $claimRepository)
    {
        $this->repository = $touringAdvanceRepository;
        $this->claimRepository = $claimRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'purpose' => 'required|string|max:255',
            'start_date' => 'required|date',
            'completion_date' => 'required|date',
            'total_amount_approved' => 'required|numeric|min:1',
            'type' => 'required|string|in:retirement',
            'category' => 'required|string|in:residence,non-residence',
        ];
    }

    public function store(array $data)
    {
        return  DB::transaction(function () use ($data) {
            $claim = $this->claimRepository->create($data);
            $advance = null;

            if ($claim) {
                $advance = parent::store([
                    'claim_id' => $claim->id,
                    'user_id' => Auth::user()->id,
                    'department_id' => Auth::user()->department_id,
                ]);
            }

            return $advance;
        });
    }
}
