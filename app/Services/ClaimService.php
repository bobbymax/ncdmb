<?php

namespace App\Services;


use App\Events\DocumentControl;
use App\Events\FirstDraft;
use App\Models\Claim;
use App\Models\Document;
use App\Repositories\ClaimRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\ExpenseRepository;
use App\Repositories\UploadRepository;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
            'expenses' => 'required|json',
            'deletedExpenses' => 'sometimes|json',
            'deletedUploads' => 'sometimes|json',
            'supporting_documents' => 'sometimes|array',
            'supporting_documents.*' => 'sometimes|file|mimes:pdf,jpg,jpeg,png',
            'workflow_id' => 'required|integer|exists:workflows,id',
            'document_category_id' => 'required|integer|exists:document_categories,id',
            'document_type_id' => 'required|integer|exists:document_types,id',
            'claimant_signature' => 'sometimes|nullable|string',
            'approval_signature' => 'sometimes|nullable|string',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $claim = parent::store([
                ...$data,
                'code' => $this->repository->generate('code', 'SC'),
                'start_date' => Carbon::parse($data['start_date']),
                'end_date' => Carbon::parse($data['end_date'])
            ]);

            if (!$claim) {
                return null;
            }

            $this->processExpenses($data['expenses'], $claim->id);
            $documentData = $this->prepareDocumentData($data, $claim);
            $document = $this->documentRepository->create($documentData);

            if ($document) {
                $this->processSupportingDocuments($data['supporting_documents'] ?? [], $document->id);
                FirstDraft::dispatch($document, Auth::user());
            }

            return $claim;
        });
    }

    /**
     * @throws \Exception
     */
    public function update(int $id, array $data, $parsed = true)
    {
        return DB::transaction(function () use ($id, $data) {
            $claim = parent::update($id, $data);

            if (!$claim) {
                return null;
            }

            if ($claim->document && isset($data['supporting_documents'])) {
                $this->processSupportingDocuments($data['supporting_documents'], $claim->document->id);
            }

            $this->processExpenses($data['expenses'] ?? '[]', $claim->id, true);

            if (!empty($data['claimant_signature'])) {
                $dataUrl = $data['claimant_signature'];
                $path = $this->uploadRepository->uploadSignature($dataUrl);
                $claim->update(['claimant_signature' => $path]);
            }

            if (!empty($data['approval_signature'])) {
                $dataUrl = $data['approval_signature'];
                $path = $this->uploadRepository->uploadSignature($dataUrl);
                $claim->update(['approval_signature' => $path]);
            }

            if (isset($data['deletedExpenses'])) {
                $this->deleteExpenses($data['deletedExpenses']);
            }

            if (isset($data['deletedUploads'])) {
                $this->deleteUploads($data['deletedUploads']);
            }

            $totalSpent = $claim->expenses->sum('total_amount_spent');
            $claim->update(['total_amount_spent' => $totalSpent]);

            return $claim;
        });
    }

    public function manipulate(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $claim = parent::update($id, $data);

            if (!$claim) {
                return null;
            }

            $document = $claim->document;

            if ($data['status'] === "registered" && $document) {
                $dataUrl = $data['claimant_signature'];
                $filePath = $this->uploadRepository->uploadSignature($dataUrl);
//                DocumentControl::dispatch($document, 'first', 1, $data['status'], $filePath);
                $this->uploadRepository->removeFile($filePath);
            }

            return $claim;
        });
    }

    /**
     * @throws ValidationException
     * @throws \Exception
     */
    protected function processExpenses(string $expensesJson, int $claimId, bool $isUpdate = false): void
    {
        try {
            $expenses = $this->validateAndParseJson($expensesJson, 'Invalid expenses format.');
            Log::info('Data Formatted First: ', $expenses);

            foreach ($expenses as &$expenseData) {
                if ($isUpdate) {
                    $existingExpense = $this->expenseRepository->getRecordByColumn('identifier', $expenseData['identifier']);
                    if ($existingExpense) {
                        Log::info('Updating Expense: ', ['id' => $existingExpense->id]);
                        $this->expenseRepository->update($existingExpense->id, $expenseData);
                    } else {
                        Log::info('Creating New Expense: ', $expenseData);
                        $this->expenseRepository->create([
                            ...$expenseData,
                            'claim_id' => $claimId,
                        ]);
                    }
                } else {
                    $expenseData['claim_id'] = $claimId;
                }
            }

            Log::info('Data Formatted: ', $expenses);

            if (!$isUpdate) {
                Log::info('Inserting Expenses: ', $expenses);
                $this->expenseRepository->insert($expenses);
            }
        } catch (\Exception $e) {
            Log::error('Exception in processExpenses: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    protected function processSupportingDocuments(array $files, int $documentId): void
    {
        $uploads = [];
        $currentTime = now();

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uniqueFileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
                $storedPath = $file->storeAs('documents/claims', $uniqueFileName, 'public');

                $uploads[] = [
                    'user_id' => Auth::id(),
                    'department_id' => Auth::user()->department_id,
                    'name' => $file->getClientOriginalName(),
                    'path' => $storedPath,
                    'size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'extension' => $file->getClientOriginalExtension(),
                    'uploadable_id' => $documentId,
                    'uploadable_type' => Document::class,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime,
                ];
            }
        }

        if (!empty($uploads)) {
            $this->uploadRepository->insert($uploads);
        }
    }

    /**
     * @throws ValidationException
     */
    protected function deleteExpenses(string $deletedExpensesJson): void
    {
        $deletedExpenses = $this->validateAndParseJson($deletedExpensesJson, 'Invalid deleted expenses format.');

        foreach ($deletedExpenses as $deletedExpense) {
            $expense = $this->expenseRepository->find($deletedExpense['id']);
            if ($expense) {
                $expense->delete();
            }
        }
    }

    /**
     * @throws ValidationException
     */
    protected function deleteUploads(string $deletedUploadsJson): void
    {
        $deletedUploads = $this->validateAndParseJson($deletedUploadsJson, 'Invalid deleted uploads format.');

        foreach ($deletedUploads as $deletedUpload) {
            $upload = $this->uploadRepository->find($deletedUpload['id']);
            if ($upload && Storage::disk('public')->exists($upload->path)) {
                Storage::disk('public')->delete($upload->path);
                $upload->delete();
            }
        }
    }

    protected function validateAndParseJson(string $json, string $errorMessage): array
    {
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            throw ValidationException::withMessages(['json' => $errorMessage]);
        }

        return $data;
    }

    protected function prepareDocumentData(array $data, Claim $claim): array
    {
        return [
            'user_id' => Auth::id(),
            'workflow_id' => $data['workflow_id'],
            'department_id' => $data['sponsoring_department_id'],
            'document_category_id' => $data['document_category_id'],
            'document_reference_id' => $data['document_reference_id'] ?? 0,
            'document_type_id' => $data['document_type_id'],
            'document_action_id' => $data['document_action_id'] ?? 0,
            'vendor_id' => $data['vendor_id'] ?? 0,
            'title' => $claim->title,
            'description' => $claim->title,
            'documentable_id' => $claim->id,
            'documentable_type' => Claim::class,
            'ref' => $this->documentRepository->generateRef($data['sponsoring_department_id'], $claim->code),
        ];
    }

    protected function validateOwnership(int $departmentId, int $userDepartmentId): void
    {
        if ($departmentId !== $userDepartmentId) {
            abort(403, 'You are not authorized to perform this action.');
        }
    }
}
