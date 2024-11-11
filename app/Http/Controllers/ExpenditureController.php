<?php

namespace App\Http\Controllers;

use App\Services\ExpenditureService;

class ExpenditureController extends Controller
{
    public function __construct(ExpenditureService $expenditureService) {
        parent::__construct($expenditureService, 'Expenditure');
    }
}
