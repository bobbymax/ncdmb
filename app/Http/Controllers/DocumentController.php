<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentResource;
use App\Services\DocumentService;
use Illuminate\Http\Request;

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

    public function generateDocument(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->success($this->service->generateDocument($request->all()));
    }

    public function queuedDocuments(string $status): \Illuminate\Http\JsonResponse
    {
        return $this->success($this->jsonResource::collection($this->service->collateDocumentsByStatus($status, $this->service->getScope())));
    }
}
