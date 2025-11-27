<?php

namespace App\Repositories;

use App\Models\Operation;

class OperationRepository extends BaseRepository
{
    public function __construct(Operation $operation) {
        parent::__construct($operation);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
