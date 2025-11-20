<?php

namespace App\Repositories;

use App\Models\LegalDocument;

class LegalDocumentRepository extends BaseRepository
{
    public function __construct(LegalDocument $legalDocument) {
        parent::__construct($legalDocument);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'document_type' => $data['document_type'] ?? 'contract_draft',
            'version' => $data['version'] ?? 1,
            'uploaded_at' => $data['uploaded_at'] ?? now(),
            'uploaded_by' => $data['uploaded_by'] ?? \Illuminate\Support\Facades\Auth::id(),
            'is_current' => $data['is_current'] ?? true,
            'requires_signature' => $data['requires_signature'] ?? false,
        ];
    }
}
