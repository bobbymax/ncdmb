<?php

namespace App\Services;

use App\Repositories\LifInstitutionRepository;

class LifInstitutionService extends BaseService
{
    public function __construct(LifInstitutionRepository $lifInstitutionRepository)
    {
        $this->repository = $lifInstitutionRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => "required|string|max:255",
            'principal_officers' => "required|string|max:255",
            'address' => "required|string|min:3",
            'phone' => "required|string|max:255",
            'email' => "required|string|email|max:255|unique:lif_institutions",
            'classification' => "required|string|max:255",
            'category' => "required|string|max:255",
        ];
    }
}
