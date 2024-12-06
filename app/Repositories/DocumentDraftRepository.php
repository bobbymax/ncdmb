<?php

namespace App\Repositories;

use App\Models\DocumentDraft;

class DocumentDraftRepository extends BaseRepository
{
    public function __construct(DocumentDraft $documentDraft) {
        parent::__construct($documentDraft);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
