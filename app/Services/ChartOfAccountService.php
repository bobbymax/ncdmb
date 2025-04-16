<?php

namespace App\Services;

use App\Repositories\ChartOfAccountRepository;

class ChartOfAccountService extends BaseService
{
    public function __construct(ChartOfAccountRepository $chartOfAccountRepository)
    {
        parent::__construct($chartOfAccountRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'account_code' => 'required',
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:asset,liability,equity,revenue,expense',
            'parent_id' => 'nullable',
            'level' => 'required|string|in:category,group,ledger',
            'status' => 'required|string|in:C,O',
            'is_postable' => 'required|integer|in:0,1',
        ];
    }
}
