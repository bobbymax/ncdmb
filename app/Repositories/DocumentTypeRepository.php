<?php

namespace App\Repositories;

use App\Models\DocumentType;
use Illuminate\Support\Str;

class DocumentTypeRepository extends BaseRepository
{
    public function __construct(DocumentType $documentType) {
        parent::__construct($documentType);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}
