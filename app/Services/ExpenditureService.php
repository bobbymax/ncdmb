<?php

namespace App\Services;

use App\Repositories\ExpenditureRepository;

class ExpenditureService extends BaseService
{
    public function __construct(ExpenditureRepository $expenditureRepository)
    {
        parent::__construct($expenditureRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'fund_id' => 'required|integer|exists:funds,id',
            'document_draft_id' => 'required|integer|min:1|exists:document_drafts,id',
            'department_id' => 'required|integer|min:1|exists:departments,id',
            'purpose' => 'required|string|min:5',
            'code' => 'required|string|min:5|unique:expenditures,code',
            'additional_info' => 'nullable|sometimes|string|min:1',
            'amount' => 'required|numeric|min:1',
            'type' => 'required|string|in:staff-payment,third-party-payment',
            'payment_category' => 'required|string|in:staff-claim,touring-advance,project,mandate,other',
            'currency' => 'required|string|in:NGN,USD,GBP,YEN,EUR',
            'cbn_current_rate' => 'sometimes|numeric|min:0',
            'status' => 'nullable|string',
            'budget_year' => 'required|integer|digits:4',
            'expenditureable_id' => 'sometimes|nullable|integer',
            'expenditureable_type' => 'sometimes|nullable|string',
        ];
    }
}
