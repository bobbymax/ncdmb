<?php

namespace App\Services;


use App\Repositories\FundRepository;
use Illuminate\Support\Facades\Auth;

class FundService extends BaseService
{
    public function __construct(FundRepository $fundRepository)
    {
        parent::__construct($fundRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'sub_budget_head_id' => 'required|integer|exists:sub_budget_heads,id',
            'department_id' => 'required|integer|exists:departments,id',
            'budget_code_id' => 'required|integer|exists:budget_codes,id',
            'total_approved_amount' => 'required|numeric|min:1',
            'budget_year' => 'required|integer|digits:4',
            'is_exhausted' => 'nullable|sometimes|boolean',
            'is_logistics' => 'nullable|sometimes|boolean',
        ];
    }

    /**
     * @throws \Exception
     */
    public function index()
    {
        return $this->indexFilter(Auth::user()->department_id, "department");
    }
}
