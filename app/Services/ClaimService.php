<?php

namespace App\Services;

use App\Http\Resources\ClaimResource;
use App\Repositories\ClaimRepository;
use App\Repositories\ExpenseRepository;
use Illuminate\Support\Facades\DB;

class ClaimService extends BaseService
{
    protected ExpenseRepository $expenseRepository;

    public function __construct(
        ClaimRepository $claimRepository,
        ClaimResource $claimResource,
        ExpenseRepository $expenseRepository
    ) {
        parent::__construct($claimRepository, $claimResource);
        $this->expenseRepository = $expenseRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'purpose' => 'required|string|max:255',
            'start_date' => 'required|date',
            'completion_date' => 'required|date',
            'total_amount_spent' => 'required|numeric|min:1',
            'total_amount_retired' => 'sometimes|numeric|min:0',
            'type' => 'required|string|in:claim',
            'category' => 'required|string|in:residence,non-residence',
            'expenses' => 'required|array',
        ];
    }

    public function store(array $data)
    {
        return  DB::transaction(function () use ($data) {
            $claim = parent::store($data);

            if ($claim) {
                if (!empty($data['expenses']) && is_array($data['expenses'])) {
                    foreach ($data['expenses'] as $expense) {
                        $expense['claim_id'] = $claim->id;
                        $this->expenseRepository->create($expense);
                    }
                }

                $total_amount_spent = $claim->expenses()->sum('total_amount_spent');
                $claimType = $claim->type;

                if ($claimType === 'retirement') {
                    $claim->update(['total_amount_retired' => $total_amount_spent]);
                } else {
                    $claim->update(['total_amount_spent' => $total_amount_spent]);
                }
            }

            return $claim;
        });
    }
}
