<?php

namespace App\Repositories;

use App\Models\Query;

class QueryRepository extends BaseRepository
{
    public function __construct(Query $query) {
        parent::__construct($query);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
