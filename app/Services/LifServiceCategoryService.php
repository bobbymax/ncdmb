<?php

namespace App\Services;

use App\Repositories\LifServiceCategoryRepository;

class LifServiceCategoryService extends BaseService
{
    public function __construct(LifServiceCategoryRepository $lifServiceCategoryRepository)
    {
        $this->repository = $lifServiceCategoryRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'lif_service_id' => 'required|integer|exists:lif_services,id',
            'name' => 'required|string|max:255',
        ];
    }
}
