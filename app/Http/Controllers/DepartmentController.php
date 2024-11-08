<?php

namespace App\Http\Controllers;

use App\Services\DepartmentService;

class DepartmentController extends Controller
{
    public function __construct(DepartmentService $departmentService) {
        $this->service = $departmentService;
        $this->name = 'Department';
    }
}
