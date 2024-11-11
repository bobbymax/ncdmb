<?php

namespace App\Http\Controllers;

use App\Services\DepartmentService;

class DepartmentController extends Controller
{
    public function __construct(DepartmentService $departmentService) {
        parent::__construct($departmentService, 'Department');
    }
}
