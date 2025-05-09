<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentResource;
use App\Services\DocumentService;

class DocumentController extends BaseController
{
    public function __construct(DocumentService $documentService) {
        parent::__construct($documentService, 'Document', DocumentResource::class);
    }

    public function getLinkedDocuments($parentDocumentId): \Illuminate\Http\JsonResponse
    {
        $document = $this->service->show($parentDocumentId);

        if (!$document) {
            return $this->error(null, "Document not found", 404);
        }

        return $this->success($this->jsonResource::collection($document->linkedDocuments));
    }
}
