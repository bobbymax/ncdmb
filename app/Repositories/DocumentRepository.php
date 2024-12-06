<?php

namespace App\Repositories;

use App\Models\Document;

class DocumentRepository extends BaseRepository
{
    public function __construct(Document $document) {
        parent::__construct($document);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
