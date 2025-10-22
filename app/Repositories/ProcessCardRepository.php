<?php

namespace App\Repositories;

use App\Models\ProcessCard;

class ProcessCardRepository extends BaseRepository
{
    public function __construct(ProcessCard $processCard) {
        parent::__construct($processCard);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
