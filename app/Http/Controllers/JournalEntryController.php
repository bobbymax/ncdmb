<?php

namespace App\Http\Controllers;


use App\Http\Resources\JournalEntryResource;
use App\Services\JournalEntryService;

class JournalEntryController extends BaseController
{
    public function __construct(JournalEntryService $journalEntryService) {
        parent::__construct($journalEntryService, 'JournalEntry', JournalEntryResource::class);
    }
}
