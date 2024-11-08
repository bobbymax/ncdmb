<?php

namespace App\Services;

use App\Repositories\LifInstitutionServiceRepository;

class LifInstitutionServiceService extends BaseService
{
    public function __construct(LifInstitutionServiceRepository $lifInstitutionServiceRepository)
    {
        $this->repository = $lifInstitutionServiceRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'lif_institution_id' => 'required|integer|exists:lif_institutions,id',
            'lif_service_id' => 'required|integer|exists:lif_services,id',
            'contractor_id' => 'required|integer|exists:companies,id',
        ];
    }
}
