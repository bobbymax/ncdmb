<?php

namespace App\Services;

use App\Repositories\VesselRepository;

class VesselService extends BaseService
{
    public function __construct(VesselRepository $vesselRepository)
    {
        $this->repository = $vesselRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'vessel_type' => 'required|string|max:255',
            'imo_no' => 'nullable|string|max:255|unique:vessels,imo_no',
            'nimasa_reg_no' => 'nullable|string|max:255|unique:vessels,nimasa_reg_no',
            'flagging' => 'nullable|string|max:255',
        ];
    }
}
