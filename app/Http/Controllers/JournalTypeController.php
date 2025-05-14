<?php

namespace App\Http\Controllers;


use App\Http\Resources\JournalTypeResource;
use App\Services\JournalTypeService;

class JournalTypeController extends BaseController
{
    public function __construct(JournalTypeService $journalTypeService) {
        parent::__construct($journalTypeService, 'JournalType', JournalTypeResource::class);
    }
}
