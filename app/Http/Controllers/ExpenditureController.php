<?php

namespace App\Http\Controllers;

use App\Services\ExpenditureService;

class ExpenditureController extends Controller
{
    public function __construct(ExpenditureService $expenditureService) {
        $this->service = $expenditureService;
        $this->name = 'Expenditure';
    }
}
