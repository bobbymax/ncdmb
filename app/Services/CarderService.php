<?php

namespace App\Services;

use App\Repositories\CarderRepository;

class CarderService extends BaseService
{
    public function __construct(CarderRepository $carderRepository)
    {
        parent::__construct($carderRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255'
        ];
    }
}
