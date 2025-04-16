<?php

namespace App\Services;

use App\Repositories\EntityRepository;

class EntityService extends BaseService
{
    public function __construct(EntityRepository $entityRepository)
    {
        parent::__construct($entityRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => "required|string|max:255",
            'acronym' => "required|string|max:255",
            'payment_code' => "required|max:255",
            'status' => "required|in:active,inactive",
        ];
    }
}
