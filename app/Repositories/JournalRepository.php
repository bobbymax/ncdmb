<?php

namespace App\Repositories;

use App\Models\Journal;

class JournalRepository extends BaseRepository
{
    public function __construct(Journal $journal) {
        parent::__construct($journal);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
