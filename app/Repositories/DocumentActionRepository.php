<?php

namespace App\Repositories;

use App\Models\DocumentAction;

class DocumentActionRepository extends BaseRepository
{
    public function __construct(DocumentAction $documentAction) {
        parent::__construct($documentAction);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
