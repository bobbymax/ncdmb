<?php

namespace App\Repositories;

use App\Models\DocumentCategory;
use Illuminate\Support\Str;

class DocumentCategoryRepository extends BaseRepository
{
    public function __construct(DocumentCategory $documentCategory) {
        parent::__construct($documentCategory);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name'])
        ];
    }
}
