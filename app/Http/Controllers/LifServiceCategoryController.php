<?php

namespace App\Http\Controllers;

use App\Services\LifServiceCategoryService;

class LifServiceCategoryController extends Controller
{
    public function __construct(LifServiceCategoryService $lifServiceCategoryService) {
        $this->service = $lifServiceCategoryService;
        $this->name = 'LifServiceCategory';
    }
}
