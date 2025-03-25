<?php

namespace App\Services;

use App\Repositories\SignatureRepository;

class SignatureService extends BaseService
{
    public function __construct(SignatureRepository $signatureRepository)
    {
        parent::__construct($signatureRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'signatory_id' => 'required|integer|exists:signatories,id',
            'user_id' => 'required|integer|exists:users,id',
            'document_draft_id' => 'required|integer|exists:documents,id',
            'signature' => 'required|string'
        ];
    }
}
