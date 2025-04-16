<?php

namespace App\Repositories;

use App\Models\JournalEntry;

class JournalEntryRepository extends BaseRepository
{
    public function __construct(JournalEntry $journalEntry) {
        parent::__construct($journalEntry);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
