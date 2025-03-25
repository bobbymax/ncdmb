<?php

namespace App\Repositories;

use App\Models\DocumentTrail;

class DocumentTrailRepository extends BaseRepository
{
    public function __construct(DocumentTrail $documentTrail) {
        parent::__construct($documentTrail);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
