<?php

namespace App\Services;

use App\Repositories\QueryRepository;

class QueryService extends BaseService
{
    public function __construct(QueryRepository $queryRepository)
    {
        parent::__construct($queryRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'group_id' => 'required|integer|exists:groups,id',
            'document_id' => 'required|integer|exists:documents,id',
            'message' => 'required|string|min:5',
            'response' => 'nullable',
            'priority' => 'required|string|in:low,medium,high',
            'status' => 'required|string|in:open,closed',
        ];
    }
}
