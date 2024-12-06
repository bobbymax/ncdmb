<?php

namespace App\Repositories;

use App\Models\DocumentType;

class DocumentTypeRepository extends BaseRepository
{
    public function __construct(DocumentType $documentType) {
        parent::__construct($documentType);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
