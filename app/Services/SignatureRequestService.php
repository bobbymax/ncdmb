<?php

namespace App\Services;

use App\Repositories\SignatureRequestRepository;

class SignatureRequestService extends BaseService
{
    public function __construct(SignatureRequestRepository $signatureRequestRepository)
    {
        parent::__construct($signatureRequestRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'document_id' => 'required|integer|exists:documents,id',
            'document_draft_id' => 'required|integer|exists:document_drafts,id',
            'department_id' => 'required|integer|exists:departments,id',
            'group_id' => 'required|integer|exists:groups,id',
            'status' => 'required|string|in:pending,accepted,declined',
        ];
    }
}
