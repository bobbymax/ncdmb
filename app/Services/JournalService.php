<?php

namespace App\Services;

use App\DTO\ProcessedIncomingData;
use App\Engine\ControlEngine;
use App\Handlers\CodeGenerationErrorException;
use App\Repositories\DocumentRepository;
use App\Repositories\JournalEntryRepository;
use App\Repositories\JournalRepository;
use App\Repositories\TransactionRepository;
use App\Traits\DocumentFlow;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class JournalService extends BaseService
{
    use DocumentFlow;

    protected DocumentRepository $documentRepository;
    protected JournalEntryRepository $journalEntryRepository;
    protected TransactionRepository $transactionRepository;
    protected ControlEngine $controlEngine;
    public function __construct(
        JournalRepository $journalRepository,
        DocumentRepository $documentRepository,
        JournalEntryRepository $journalEntryRepository,
        TransactionRepository $transactionRepository,
        ControlEngine $controlEngine
    ) {
        parent::__construct($journalRepository);
        $this->documentRepository = $documentRepository;
        $this->journalEntryRepository = $journalEntryRepository;
        $this->transactionRepository = $transactionRepository;
        $this->controlEngine = $controlEngine;
    }

    public function rules($action = "store"): array
    {
        return [
            'created_by' => 'required|integer|exists:users,id',
            'department_id' => 'required|integer|exists:departments,id',
            'description' => 'required|string',
            'transaction_date' => 'required|date',
            'journalable_id' => 'required|integer',
            'journalable_type' => 'required|string',
            'journal_entries' => 'required|array',
        ];
    }

    /**
     * @throws CodeGenerationErrorException
     * @throws \Exception
     */
    public function publish(ProcessedIncomingData $processedIncomingData): bool
    {
        return DB::transaction(function () use ($processedIncomingData) {
            $proc = processor();
            $document = $proc->resourceResolver($processedIncomingData->document_id, 'document');
            $payment = $proc->resourceResolver($processedIncomingData->document_resource_id, 'payment');
            $resource = $proc->resourceResolver($processedIncomingData->state['resource_id'], $processedIncomingData->entity_type);

            if (!$document || !$resource) {
                return false;
            }

            $message = "Journal Report Posting for {$resource->code}";

            $journalData = $this->prepareJournal(
                $processedIncomingData->user_id,
                $resource->department_id,
                $message,
                $payment
            );

            $journal = parent::store($journalData);

            // Build enriched transactions
            $entries = collect($processedIncomingData->resources)->map(function ($item) {
                return $item['raw'] ?? [];
            });

            if ($journal) {
                $entries->each(function ($item) use ($journal, $processedIncomingData) {
                    $transaction = $this->transactionRepository->update($item['id'], [
                        ...$item,
                        'status' => $processedIncomingData->status,
                    ]);

                    if ($transaction) {
                        $this->journalEntryRepository->create([
                            'journal_id' => $journal->id,
                            'user_id' => $processedIncomingData->user_id,
                            'department_id' => $processedIncomingData->department_id,
                            'transaction_id' => $transaction->id,
                            'chart_of_account_id' => $transaction->chart_of_account_id,
                            'collectable_id' => $transaction->beneficiary_id,
                            'collectable_type' => $transaction->beneficiary_type,
                        ]);
                    }
                });

                $payment->update([
                    'status' => 'posted'
                ]);

                $payment->document->update([
                    'status' => $processedIncomingData->status,
                ]);

                $resource->update([
                    'status' => $processedIncomingData->status,
                ]);

                $resource->document->update([
                    'status' => $processedIncomingData->status,
                ]);

                $this->createDocumentForJournal($journal, $payment, $processedIncomingData);
            }

            return true;
        });
    }

    /**
     * @throws \Exception
     */
    protected function createDocumentForJournal(
        $journal,
        $payment,
        ProcessedIncomingData $processedIncomingData
    ) {

        $documentProperties = [
            'workflow_id' => $processedIncomingData->workflow_id,
            'department_id' => $processedIncomingData->department_id,
            'document_category_id' => $this->getResourceId($processedIncomingData->document_category, 'documentcategory'),
            'document_type_id' => $this->getResourceId($processedIncomingData->document_type, 'documenttype'),
            'document_action_id' => $processedIncomingData->document_action_id,
            'relationship_type' => 'journal_report',
            'linked_document' => $payment->document ?? null,
        ];

        $documentData = $this->documentRepository->build(
            $documentProperties,
            $journal,
            $processedIncomingData->department_id,
            "Journal Posting for {$payment->narration}",
            $journal->description,
            true,
            $processedIncomingData->trigger_workflow_id,
            $this->workflowArgs(
                JournalService::class,
                $processedIncomingData->document_action_id ?? 0,
                $journal,
                $payment->total_amount_paid
            ),
            [],
            "journal_no"
        );

        return $this->documentRepository->create($documentData);
    }

    protected function getResourceId($value, $service)
    {
        $resource = processor()->resourceResolver(
            $value,
            $service,
            ['column' => 'label']
        );
        return $resource ? $resource->id : 0;
    }

    /**
     * @throws CodeGenerationErrorException
     */
    private function prepareJournal(
        int $userId,
        int $departmentId,
        string $description,
        mixed $payment
    ): array {
        return [
            'journal_no' => $this->generate('journal_no', 'JN'),
            'created_by' => $userId,
            'department_id' => $departmentId,
            'description' => $description,
            'transaction_date' => Carbon::parse($payment->updated_at),
            'journalable_id' => $payment->id,
            'journalable_type' => get_class($payment),
        ];
    }
}
