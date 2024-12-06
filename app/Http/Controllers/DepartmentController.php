<?php

namespace App\Http\Controllers;

use App\Http\Resources\DepartmentResource;
use App\Services\DepartmentService;

class DepartmentController extends BaseController
{
    public function __construct(DepartmentService $departmentService) {
        parent::__construct($departmentService, 'Department', DepartmentResource::class);
    }
}
