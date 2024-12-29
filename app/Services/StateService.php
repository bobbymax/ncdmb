<?php

namespace App\Services;

use App\Repositories\StateRepository;

class StateService extends BaseService
{
    public function __construct(StateRepository $stateRepository)
    {
        parent::__construct($stateRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255'
        ];
    }
}
