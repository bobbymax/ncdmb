<?php

namespace App\Http\Controllers;

use App\Http\Resources\ExpenditureResource;
use App\Services\ExpenditureService;

class ExpenditureController extends BaseController
{
    public function __construct(ExpenditureService $expenditureService) {
        parent::__construct($expenditureService, 'Expenditure', ExpenditureResource::class);
    }
}
