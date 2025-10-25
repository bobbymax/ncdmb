<?php

namespace App\Repositories;

use App\Models\DocumentPanel;
use Illuminate\Support\Str;

class DocumentPanelRepository extends BaseRepository
{
    public function __construct(DocumentPanel $documentPanel) {
        parent::__construct($documentPanel);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
            'document_category_id' => $data['document_category_id'] < 1 ? null : $data['document_category_id'],
        ];
    }
}
