<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentDraftResource;
use App\Http\Resources\ExpenditureResource;
use App\Http\Resources\ExpenseResource;
use App\Services\DocumentDraftService;
use Illuminate\Support\Facades\Auth;

class DocumentDraftController extends BaseController
{
    public function __construct(DocumentDraftService $documentDraftService) {
        parent::__construct($documentDraftService, 'DocumentDraft', DocumentDraftResource::class);
    }

    public function drafts(string $status): \Illuminate\Http\JsonResponse
    {
        $expenditures = $this->service->fetchDraftsInBatchQueue(Auth::user()->department_id, $status);
        return $this->success(ExpenditureResource::collection($expenditures));
    }
}
