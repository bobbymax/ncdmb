<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentDraftResource;
use App\Services\DocumentDraftService;
use Illuminate\Support\Facades\Auth;

class DocumentDraftController extends BaseController
{
    public function __construct(DocumentDraftService $documentDraftService) {
        parent::__construct($documentDraftService, 'DocumentDraft', DocumentDraftResource::class);
    }

    public function drafts(string $status): \Illuminate\Http\JsonResponse
    {
        $drafts = $this->service->reliesOnStatus(Auth::user()->department_id, $status);
        return $this->success($this->jsonResource::collection($drafts));
    }
}
