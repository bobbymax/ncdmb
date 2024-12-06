<?php

namespace App\Services;

use App\Repositories\DocumentDraftRepository;

class DocumentDraftService extends BaseService
{
    public function __construct(DocumentDraftRepository $documentDraftRepository)
    {
        parent::__construct($documentDraftRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
