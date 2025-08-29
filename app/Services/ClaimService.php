<?php

namespace App\Services;


use App\DTO\ProcessedIncomingData;
use App\Handlers\CodeGenerationErrorException;
use App\Handlers\DataNotFound;
use App\Models\Document;
use App\Repositories\{ClaimRepository, DocumentRepository, ExpenseRepository, UploadRepository};
use App\Traits\DocumentFlow;
use Carbon\Carbon;
use Illuminate\Support\Facades\{Auth, DB, Log};
use Illuminate\Support\Arr;
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

    /**
     * @param array $data
     * @return array
     */
    public function resolveContent(array $data): array
    {
        $resolvedContents = [
            'title' => null,
            'claim' => null,
            'expenses' => null,
            'total_amount_spent' => 0
        ];

        foreach ($data['content'] as $item) {
            // Paper Title
            if (isset($item['content']['paper_title'])) {
                $resolvedContents['title'] = $item['content']['paper_title']['title'] ?? 'Unknown Title';
            }

            // Expenses
            if (isset($item['content']['expense'])) {
                $expenseBlock = $item['content']['expense'];

                $resolvedContents['claim'] = $expenseBlock['claim'] ?? null;
                $resolvedContents['expenses']   = $expenseBlock['expenses'] ?? null;

                // Sum total_amount_spent
                $resolvedContents['total_amount_spent'] = collect($expenseBlock['expenses'] ?? [])
                    ->sum(function ($expense) {
                        return (float) $expense['total_amount_spent'];
                    });
            }
        }

        return [
            'document_owner' => $data['document_owner'],
            'department_owner' => $data['department_owner'],
            'category' => $data['category'] ?? null,
            'contents' => $resolvedContents
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

    /**
     * @throws CodeGenerationErrorException
     * @throws \Exception
     */
    public function buildDocumentFromTemplate(array $data)
    {
       return DB::transaction(function () use ($data) {
           $resolved = $this->resolveContent($data);

           $start_date = $resolved['contents']['claim']['start_date'] ?? null;
           $end_date = $resolved['contents']['claim']['end_date'] ?? null;
           $document_owner = $resolved['contents']['claim']['document_owner'] ?? null;
           $department_owner = $resolved['contents']['claim']['department_owner'] ?? null;

           $claimData = [
               'user_id' => $document_owner['value'] ?? Auth::id(),
               'department_id' => $department_owner['value'] ?? Auth::user()->department_id,
               'sponsoring_department_id' => $resolved['contents']['claim']['sponsoring_department_id'] ?? Auth::user()->department_id,
               'document_category_id' => $resolved['category']['id'] ?? null,
               'code' => $this->repository->generate('code', 'SC'),
               'title' => $resolved['contents']['title'] ?? null,
               'start_date' => $start_date ? Carbon::parse($start_date) : null,
               'end_date' => $end_date ? Carbon::parse($end_date) : null,
               'total_amount_spent' => $resolved['contents']['total_amount_spent'] ?? 0,
               'type' => 'claim',
               'status' => 'generated'
           ];

           $claim = parent::store($claimData);

           if (!$claim) {
               return null;
           }

           $expenses = $resolved['contents']['expenses'] ?? [];
           $this->syncExpenses($claim, $expenses);

           return $claim;
       });
    }

    /**
     * @throws DataNotFound
     * @throws \Exception
     */
    public function syncExpenses(mixed $claim, array $incomingExpenses): void
    {
        foreach ($incomingExpenses as $expenseData) {
            $existing = $this->expenseRepository->getRecordByColumn('identifier', $expenseData['identifier']);
            if ($existing) {
                // Remove fields that should not trigger update check (like timestamps)
                $compareExisting = collect($existing->toArray())
                    ->only(array_keys($expenseData))
                    ->toArray();

                // Compare current DB record with incoming values
                if ($this->hasChanged($compareExisting, $expenseData)) {
                    $this->expenseRepository->update($existing->id, $expenseData);
                }
            } else {
                // Create if it doesn't exist
                $this->expenseRepository->create([
                    ...$expenseData,
                    'claim_id' => $claim->id,
                    'start_date' => Carbon::parse($expenseData['start_date']),
                    'end_date' => Carbon::parse($expenseData['end_date']),
                ]);
            }
        }
    }

    /**
     * Check if any field value has changed.
     */
    protected function hasChanged(array $existing, array $incoming): bool
    {
        foreach ($incoming as $key => $value) {
            // Normalize numbers and dates to avoid false positives
            $old = is_numeric($existing[$key] ?? null) ? (float) $existing[$key] : $existing[$key] ?? null;
            $new = is_numeric($value) ? (float) $value : $value;

            if ($old != $new) {
                return true;
            }
        }
        return false;
    }

    public function consolidate(ProcessedIncomingData $data): mixed
    {
        return DB::transaction(function () use ($data) {
            $document = processor()->resourceResolver($data->document_id, 'document');
            if (!$document) return null;

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

            $claim->document->update([
                'status' => "expenses-reconciled"
            ]);

            $claim->expenditure->payment->document->update([
                'status' => "expenses-reconciled"
            ]);

            $lastDraft = $claim->expenditure->payment->document->drafts()->latest()->first();

            if ($lastDraft) {
                $lastDraft->update([
                    'status' => "expenses-reconciled"
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
            $claim->sponsoring_department_id,
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
