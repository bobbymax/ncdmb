<?php

namespace App\Repositories;

use App\Models\MaterialType;

class MaterialTypeRepository extends BaseRepository
{
    public function __construct(MaterialType $materialType) {
        parent::__construct($materialType);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
