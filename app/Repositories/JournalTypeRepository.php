<?php

namespace App\Repositories;

use App\Models\JournalType;

class JournalTypeRepository extends BaseRepository
{
    public function __construct(JournalType $journalType) {
        parent::__construct($journalType);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
