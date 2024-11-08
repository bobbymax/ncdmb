<?php

namespace App\Services;

use App\Repositories\CDActivityRepository;

class CDActivityService extends BaseService
{
    public function __construct(CDActivityRepository $cDActivityRepository)
    {
        $this->repository = $cDActivityRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
