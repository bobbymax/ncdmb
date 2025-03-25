<?php

namespace App\Repositories;

use App\Models\Signatory;

class SignatoryRepository extends BaseRepository
{
    public function __construct(Signatory $signatory) {
        parent::__construct($signatory);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
