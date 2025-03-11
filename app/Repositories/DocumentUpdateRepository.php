<?php

namespace App\Repositories;

use App\Models\DocumentUpdate;
use Illuminate\Support\Facades\Auth;

class DocumentUpdateRepository extends BaseRepository
{
    public function __construct(DocumentUpdate $documentUpdate) {
        parent::__construct($documentUpdate);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'threads' => isset($data['threads']) ? json_encode($data['threads']) : null,
            'user_id' => Auth::id()
        ];
    }
}
