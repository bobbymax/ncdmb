<?php

namespace App\Repositories;

use App\Models\ProcuredMaterial;

class ProcuredMaterialRepository extends BaseRepository
{
    public function __construct(ProcuredMaterial $procuredMaterial) {
        parent::__construct($procuredMaterial);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
