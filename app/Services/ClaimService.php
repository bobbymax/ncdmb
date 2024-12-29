<?php

namespace App\Services;


use App\Models\Claim;
use App\Models\Document;
use App\Models\Upload;
use App\Repositories\ClaimRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\ExpenseRepository;
use App\Repositories\TripRepository;
use App\Repositories\UploadRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClaimService extends BaseService
{
    protected ExpenseRepository $expenseRepository;
    protected UploadRepository $uploadRepository;
    protected DocumentRepository $documentRepository;

    public function __construct(
        ClaimRepository $claimRepository,
        ExpenseRepository $expenseRepository,
        UploadRepository $uploadRepository,
        DocumentRepository $documentRepository,
    ) {
        parent::__construct($claimRepository);
        $this->expenseRepository = $expenseRepository;
        $this->uploadRepository = $uploadRepository;
        $this->documentRepository = $documentRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'sponsoring_department_id' => 'required|integer|exists:departments,id',
            'total_amount_spent' => 'required|numeric|min:1',
            'total_amount_retired' => 'sometimes|numeric|min:0',
            'total_amount_approved' => 'sometimes|numeric|min:0',
            'type' => 'required|string|in:claim',
            'expenses' => 'required|array',
            'supporting_documents' => 'required|array',
            'supporting_documents.*' => 'required|file|mimes:pdf,jpeg,jpg,png|max:4096',
            'workflow_id' => 'required|integer|exists:workflows,id',
            'document_category_id' => 'required|integer|exists:document_categories,id',
            'document_type_id' => 'required|integer|exists:document_types,id',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $claim = parent::store($data);

            if ($claim) {

                if (!empty($data['expenses']) && is_array($data['expenses'])) {
                    foreach ($data['expenses'] as $expense) {
                        $expense['claim_id'] = $claim->id;
                        $this->expenseRepository->create($expense);
                    }
                }

                $documentData = [
                    'user_id' => Auth::user()->id,
                    'department_id' => $data['sponsoring_department_id'],
                    'document_category_id' => $data['document_category_id'],
                    'document_type_id' => $data['document_type_id'],
                    'title' => "Expense Analysis Document",
                    'description' => $claim->title,
                    'documentable_id' => $claim->id,
                    'documentable_type' => Claim::class,
                    'ref' => $this->documentRepository->generateRef($data['sponsoring_department_id'], $claim->code)
                ];

                $document = $this->documentRepository->create($documentData);

                if ($document) {
                    if (isset($data['supporting_documents']) && is_array($data['supporting_documents'])) {
                        foreach ($data['supporting_documents'] as $file) {
                            if ($file instanceof UploadedFile) {
                                $path = $file->store('claims', 'public');

                                $uploadData = [
                                    'name' => $file->getClientOriginalName(),
                                    'path' => $path,
                                    'size' => $file->getSize(),
                                    'mime_type' => $file->getClientMimeType(),
                                    'extension' => $file->getClientOriginalExtension(),
                                    'uploadable_id' => $document->id,
                                    'uploadable_type' => Document::class,
                                ];

                                $this->uploadRepository->create($uploadData);
                            }
                        }
                    }

                    // Trigger Document Created Event!!!
                }
            }

            return $claim;
        });
    }
}
