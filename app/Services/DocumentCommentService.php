<?php

namespace App\Services;

use App\Repositories\DocumentCommentRepository;

class DocumentCommentService extends BaseService
{
    public function __construct(DocumentCommentRepository $documentCommentRepository)
    {
        parent::__construct($documentCommentRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'document_draft_id' => 'required|integer|exists:document_drafts,id',
            'user_id' => 'required|integer|exists:users,id',
            'comment' => 'required|string|min:3',
            'parent_id' => 'sometimes|integer|min:0',
            'status' => 'required|string|in:action-required,attention-required,kiv,prayer,other'
        ];
    }
}
