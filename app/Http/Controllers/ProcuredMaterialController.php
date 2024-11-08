<?php

namespace App\Http\Controllers;

use App\Services\ProcuredMaterialService;

class ProcuredMaterialController extends Controller
{
    public function __construct(ProcuredMaterialService $procuredMaterialService) {
        $this->service = $procuredMaterialService;
        $this->name = 'ProcuredMaterial';
    }
}
