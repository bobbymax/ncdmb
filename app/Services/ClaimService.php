<?php

namespace App\Services;


use App\Engine\ControlEngine;
use App\Engine\Puzzle;
use App\Support\Builders\DocumentDataBuilder;
use App\Models\{Claim, Document};
use App\Repositories\{ClaimRepository, DocumentRepository, ExpenseRepository, UploadRepository};
use App\Traits\DocumentFlow;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Auth, DB, Log, Storage};
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClaimService extends BaseService
{
    use DocumentFlow;
    protected ExpenseRepository $expenseRepository;
    protected UploadRepository $uploadRepository;
    protected DocumentRepository $documentRepository;
    protected ControlEngine $engine;

    public function __construct(
        ClaimRepository $claimRepository,
        ExpenseRepository $expenseRepository,
        UploadRepository $uploadRepository,
        DocumentRepository $documentRepository,
        ControlEngine $engine
    ) {
        parent::__construct($claimRepository);
        $this->expenseRepository = $expenseRepository;
        $this->uploadRepository = $uploadRepository;
        $this->documentRepository = $documentRepository;
        $this->engine = $engine;
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
                'end_date' => Carbon::parse($data['end_date']),
                'user_id' => Auth::id()
            ]);

            if (!$claim) {
                return null;
            }

            $this->processExpenses($data['expenses'], $claim->id);
            $documentData = $this->documentRepository->build(
                $data,
                $claim,
                $data['sponsoring_department_id'],
                $claim->title,
                $claim->title
            );
            $document = $this->documentRepository->create($documentData);

            if ($document) {
                $this->processSupportingDocuments($data['supporting_documents'] ?? [], $document->id);

                $this->engine->initialize(
                    $this,
                    $document,
                    $document->workflow,
                    $document->current_tracker,
                    $this->getCreationDocumentAction(),
                    $this->setStateValues($claim->id),
                    null,
                    null,
                    $claim->total_amount_spent
                );

                $this->engine->process();
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
                $path = $this->signatureUpload($dataUrl);
                $claim->update(['claimant_signature' => $path]);
            }

            if (!empty($data['approval_signature'])) {
                $dataUrl = $data['approval_signature'];
                $path = $this->signatureUpload($dataUrl);
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
        $this->uploadRepository->uploadMany(
            $files,
            $documentId,
            Document::class
        );
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
            $upload->delete();
//            if ($upload && Storage::disk('public')->exists($upload->path)) {
//                Storage::disk('public')->delete($upload->path);
//            }
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

    public function destroy(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $claim = $this->repository->find($id);

            if (!$claim) {
                return false;
            }

            $claim->expenses()->delete();
            parent::destroy($id);

            return true;
        });
    }
}
