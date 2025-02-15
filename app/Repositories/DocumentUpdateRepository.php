<?php

namespace App\Repositories;

use App\Models\DocumentUpdate;

class DocumentUpdateRepository extends BaseRepository
{
    public function __construct(DocumentUpdate $documentUpdate) {
        parent::__construct($documentUpdate);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'threads' => json_encode($data['threads'])
        ];
    }
}
