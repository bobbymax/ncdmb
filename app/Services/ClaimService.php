<?php

namespace App\Services;


use App\DTO\ProcessedIncomingData;
use App\Models\Document;
use App\Repositories\{ClaimRepository, DocumentRepository, ExpenseRepository, UploadRepository};
use App\Traits\DocumentFlow;
use Carbon\Carbon;
use Illuminate\Support\Facades\{Auth, DB, Log};
use Illuminate\Validation\ValidationException;

class ClaimService extends BaseService
{
    use DocumentFlow;
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
            'approval_document_id' => 'sometimes|nullable|integer',
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
            $this->createDocumentForClaim($claim, $data);

            return $claim;
        });
    }

    public function consolidate(ProcessedIncomingData $data): mixed
    {
        return DB::transaction(function () use ($data) {
            $expClaimId = 0;

            foreach ($data->resources as $exp) {
                $expRaw = $exp['raw'];
                $expense = $this->expenseRepository->find($expRaw['id']);

                if (!$expense) continue;
                if ($expClaimId === 0) {
                    $expClaimId = $expense->claim_id;
                }

                $expense->update([
                    ...$expRaw,
                    'status' => $exp['status'],
                    'variation_type' => $exp['actionPerformed'],
                ]);
            }

            $claim = $this->show($expClaimId);

            if (!$claim) return null;

            if ($claim->expenditure) {
                $claim->expenditure->update([
                    'amount' => $claim->expenses->sum('total_amount_paid'),
                ]);
            }

            return $claim->document;
        });
    }

    public function compute($record)
    {
        return $record ? $record->expenses->sum('total_amount_paid') : 0;
    }

    /**
     * @throws \Exception
     */
    protected function createDocumentForClaim($claim, array $data)
    {
        $documentData = $this->documentRepository->build(
            [
                ...$data,
                'linked_document' => isset($data['approval_document_id']) ? $this->getDocument($data['approval_document_id']) : null,
                'relationship_type' => 'approval_memo'
            ],
            $claim,
            $data['sponsoring_department_id'],
            $claim->title,
            $claim->title,
            true,
            null,
            $this->workflowArgs(
                ClaimService::class,
                $data['document_action_id'] ?? 0,
                $claim,
                $claim->total_amount_spent
            ),
            $data['supporting_documents'] ?? []
        );

        return $this->documentRepository->create($documentData);
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
                $this->documentRepository->processSupportingDocuments($data['supporting_documents'], $claim->document->id);
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
