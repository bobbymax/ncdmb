<?php

namespace App\Services;

use App\Models\Signatory;
use App\Repositories\SignatoryRepository;

class SignatoryService extends BaseService
{
    public function __construct(SignatoryRepository $signatoryRepository)
    {
        parent::__construct($signatoryRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'page_id' => 'required|integer|exists:pages,id',
            'group_id' => 'required|integer|exists:groups,id',
            'department_id' => 'required|integer|min:0',
            'type' => 'required|string|in:' . implode(',', Signatory::$type),
            'order' => 'required|integer|min:1',
        ];
    }
}
