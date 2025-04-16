<?php

namespace App\Http\Controllers;


use App\Http\Resources\JournalResource;
use App\Services\JournalService;

class JournalController extends BaseController
{
    public function __construct(JournalService $journalService) {
        parent::__construct($journalService, 'Journal', JournalResource::class);
    }
}
