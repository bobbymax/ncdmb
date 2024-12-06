<?php

namespace App\Repositories;

use App\Models\DocumentCategory;

class DocumentCategoryRepository extends BaseRepository
{
    public function __construct(DocumentCategory $documentCategory) {
        parent::__construct($documentCategory);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
