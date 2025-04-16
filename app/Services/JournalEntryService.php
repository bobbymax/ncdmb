<?php

namespace App\Services;

use App\Repositories\JournalEntryRepository;

class JournalEntryService extends BaseService
{
    public function __construct(JournalEntryRepository $journalEntryRepository)
    {
        parent::__construct($journalEntryRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
