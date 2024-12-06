<?php

namespace App\Repositories;

use App\Models\DocumentComment;

class DocumentCommentRepository extends BaseRepository
{
    public function __construct(DocumentComment $documentComment) {
        parent::__construct($documentComment);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
