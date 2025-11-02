<?php

namespace App\Repositories;

use App\Models\Inbound;

class InboundRepository extends BaseRepository
{
    public function __construct(Inbound $inbound) {
        parent::__construct($inbound);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
