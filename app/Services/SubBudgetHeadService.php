<?php

namespace App\Services;

use App\Http\Resources\SubBudgetHeadResource;
use App\Repositories\SubBudgetHeadRepository;

class SubBudgetHeadService extends BaseService
{
    public function __construct(SubBudgetHeadRepository $subBudgetHeadRepository, SubBudgetHeadResource $subBudgetHeadResource)
    {
        parent::__construct($subBudgetHeadRepository, $subBudgetHeadResource);
    }

    public function rules($action = "store"): array
    {
        return [
            'budget_head_id' => 'required|integer|exists:budget_heads,id',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:capital,recurrent,personnel',
            'is_logistics' => 'sometimes|boolean',
            'is_blocked' => 'sometimes|boolean',
        ];
    }
}
