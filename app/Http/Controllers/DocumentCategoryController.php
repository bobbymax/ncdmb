<?php

namespace App\Http\Controllers;


use App\Http\Resources\DocumentCategoryResource;
use App\Services\DocumentCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DocumentCategoryController extends BaseController
{
    public function __construct(DocumentCategoryService $documentCategoryService) {
        parent::__construct($documentCategoryService, 'DocumentCategory', DocumentCategoryResource::class);
    }

    public function addSignatories(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'document_category_id' => 'required|exists:document_categories,id',
            'signatories' => 'required|array',
            'signatories.*.group_id' => 'required|integer|exists:groups,id',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Please fix the Following Errors: ', 422);
        }

        $category = $this->service->addSignatories($request->all());

        if (!$category) {
            return $this->error(null, 'Something went wrong', 500);
        }

        return $this->success(new $this->jsonResource($category), 'Signatories added successfully.');
    }
}
