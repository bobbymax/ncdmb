<?php

namespace App\Services;

use App\Repositories\DocumentTrailRepository;

class DocumentTrailService extends BaseService
{
    public function __construct(DocumentTrailRepository $documentTrailRepository)
    {
        parent::__construct($documentTrailRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'document_id' => 'required|integer',
            'document_action_id' => 'required|integer|exists:document_actions,id',
            'user_id' => 'required|integer|exists:users,id',
            'document_draft_id' => 'required|integer',
            'reason' => 'required|string|min:3',
            'document_trailable_id' => 'required|integer',
            'document_trailable_type' => 'required|string',
        ];
    }
}
