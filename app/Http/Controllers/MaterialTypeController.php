<?php

namespace App\Http\Controllers;

use App\Services\MaterialTypeService;

class MaterialTypeController extends Controller
{
    public function __construct(MaterialTypeService $materialTypeService) {
        $this->service = $materialTypeService;
        $this->name = 'MaterialType';
    }
}
