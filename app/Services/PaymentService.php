<?php

namespace App\Services;

use App\DTO\PaymentConsolidatedIncomingData;
use App\DTO\ProcessedIncomingData;
use App\Handlers\CodeGenerationErrorException;
use App\Models\Expenditure;
use App\Models\ProcessCard;
use App\Repositories\DocumentRepository;
use App\Repositories\ExpenditureRepository;
use App\Repositories\PaymentRepository;
use App\Repositories\ProgressTrackerRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\WorkflowRepository;
use App\Traits\DocumentFlow;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService extends BaseService
{
    use DocumentFlow;
    protected ExpenditureRepository $expenditureRepository;
    protected TransactionRepository $transactionRepository;
    protected DocumentRepository $documentRepository;
    protected WorkflowRepository $workflowRepository;
    protected ProgressTrackerRepository $progressTrackerRepository;
    public function __construct(
        PaymentRepository $paymentRepository,
        ExpenditureRepository $expenditureRepository,
        TransactionRepository $transactionRepository,
        DocumentRepository $documentRepository,
        WorkflowRepository $workflowRepository,
        ProgressTrackerRepository $progressTrackerRepository,
    ) {
        parent::__construct($paymentRepository);
        $this->expenditureRepository = $expenditureRepository;
        $this->transactionRepository = $transactionRepository;
        $this->documentRepository = $documentRepository;
        $this->workflowRepository = $workflowRepository;
        $this->progressTrackerRepository = $progressTrackerRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'payment_batch_id' => 'required|integer|exists:payment_batches,id',
            'document_id' => 'required|integer|exists:documents,id',
            'document_draft_id' => 'required|integer|exists:document_drafts,id',
            'document_action_id' => 'required|integer|exists:document_actions,id',
            'department_id' => 'required|integer|exists:departments,id',
            'user_id' => 'required|integer|exists:users,id',
            'ledger_id' => 'required|integer|exists:ledgers,id',
            'account_code_id' => 'required|integer|exists:chart_of_accounts,id',
            'period' => 'required|date',
            'budget_year' => 'required|integer|digits:4',
            'document_category' => 'required|string|max:255',
            'document_type' => 'required|string|max:255',
            'entity_id' => 'required|integer',
            'entity_type' => 'required|string',
            'trigger_workflow_id' => 'required|integer|exists:workflows,id',
            'progress_tracker_id' => 'required|integer|exists:progress_trackers,id',
            'transaction_type_id' => 'required|string|in:debit,credit',
            'paymentIds' => 'required|array',
            'paymentIds.*' => 'integer|exists:expenditures,id',
            'paid_at' => 'nullable|date',
            'type' => 'required|string|in:staff,third-party',
            'status' => 'required|string|in:draft,posted,reversed',
        ];
    }

    public function documentProcessor(array $data, string $mode = "update")
    {
        return DB::transaction(function () use ($data, $mode) {
            if ($mode === "store") {
                if (!empty($data)) {

                foreach ($data as &$d) {
                    // Extract journal types before unsetting (if provided from frontend)
                    $journalTypes = $d['journal_types'] ?? null;

                    // Remove fields that shouldn't be stored in payment table
                    unset($d['document_category_id']);
                    unset($d['document_type_id']);
                    unset($d['id']);
                    unset($d['payable_id']);
                    unset($d['payable_type']);
                    unset($d['payment_method']);
                    unset($d['journal_types']);  // Don't store in payment table
                    unset($d['process_metadata']); // Don't store raw metadata

                    // Create payment record
                    $payment = parent::store($d);

                    // If auto-settlement is enabled and journal types provided, execute accounting cycle
                    if ($payment && ($d['auto_generated'] ?? false) && !empty($journalTypes)) {
                        try {
                            // Get process card for this payment
                            $processCard = $payment->processCard;

                            if ($processCard && ($processCard->rules['generate_transactions'] ?? false)) {
                                // Execute accounting cycle with frontend journal types
                                app(\App\Services\ProcessCardExecutionService::class)
                                    ->executeAccountingCycle($payment, $processCard, $journalTypes);
                            }
                        } catch (\Exception $e) {
                            // Log error but don't fail the payment creation
                            \Log::error('Auto-settlement failed', [
                                'payment_id' => $payment->id,
                                'error' => $e->getMessage(),
                                'trace' => $e->getTraceAsString(),
                            ]);

                            // Optionally re-throw if you want to fail the entire transaction
                            // throw $e;
                        }
                    }
                }
                    unset($d);
                }
            }


            return true;
        });
    }

    /**
     * Process treasury clearance with journal types from frontend
     * Handles payment settlements with user-selected journal types
     *
     * @param array $data Clearance data from EmployeeTreasuryClear component
     * @return array Processing results
     * @throws \Exception If validation fails or processing errors occur
     */
    public function processPaymentClearance(array $data, ?string $mode = "update"): array
    {
        $payments = $data['payments'] ?? [];
        $processCardId = $data['process_card_id'] ?? null;
        $documentDraftId = $data['document_draft_id'] ?? null;
        $summary = $data['summary'] ?? null;

        if (empty($payments)) {
            throw new \Exception('No payments provided for clearance');
        }

        // Get process card
        $processCard = ProcessCard::find($processCardId);
        if (!$processCard) {
            throw new \Exception("ProcessCard not found: {$processCardId}");
        }

        $results = [];
        $successCount = 0;
        $errorCount = 0;

        foreach ($payments as $paymentData) {
            $paymentId = $paymentData['payment_id'] ?? null;
            $frontendTransactions = $paymentData['transactions'] ?? [];
            $expenditureId = $paymentData['expenditure_id'] ?? null;

            if (!$paymentId) {
                Log::warning('Payment data missing payment_id', ['data' => $paymentData]);
                continue;
            }

            if (empty($frontendTransactions)) {
                Log::warning('Payment data missing transactions', ['payment_id' => $paymentId]);
                $results[] = [
                    'payment_id' => $paymentId,
                    'status' => 'error',
                    'error' => 'No transactions provided for payment'
                ];
                $errorCount++;
                continue;
            }

            // Get payment from database
            $payment = $this->repository->find($paymentId);

            if (!$payment) {
                $results[] = [
                    'payment_id' => $paymentId,
                    'status' => 'error',
                    'error' => 'Payment not found'
                ];
                $errorCount++;
                continue;
            }

            try {
                // Execute accounting cycle with frontend-generated transactions
                $steps = app(\App\Services\ProcessCardExecutionService::class)
                    ->executeAccountingCycleWithTransactions($payment, $processCard, $frontendTransactions);

                $results[] = [
                    'payment_id' => $paymentId,
                    'payment_code' => $payment->code,
                    'amount' => $paymentData['amount'],
                    'status' => 'success',
                    'steps_completed' => count($steps),
                    'steps' => $steps,
                    'transactions_saved' => count($frontendTransactions),
                ];

                $successCount++;

                // Create Payment Document Here!!!!

                Log::info('Payment clearance executed successfully', [
                    'payment_id' => $payment->id,
                    'payment_code' => $payment->code,
                    'transactions_count' => count($frontendTransactions),
                    'steps_count' => count($steps),
                ]);

            } catch (\Exception $e) {
                $results[] = [
                    'payment_id' => $paymentId,
                    'payment_code' => $payment->code ?? "Unknown",
                    'status' => 'error',
                    'error' => $e->getMessage(),
                ];
                $errorCount++;

                Log::error('Payment clearance failed', [
                    'payment_id' => $paymentId,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);

                // Optional: Fail entire batch on first error
                // throw $e;
            }
        }

        // Update document draft status if all successful
        if ($documentDraftId && $errorCount === 0) {
            \App\Models\DocumentDraft::find($documentDraftId)?->update([
                'status' => 'processed',
                'processed_at' => now(),
                'processed_by' => Auth::id(),
                'metadata' => [
                    'clearance_summary' => $summary,
                    'processed_payments' => $successCount,
                ],
            ]);
        }

        Log::info('Treasury clearance batch completed', [
            'total_payments' => count($payments),
            'successful' => $successCount,
            'failed' => $errorCount,
            'summary' => $summary,
        ]);

        return [
            'success' => $errorCount === 0,
            'total_processed' => count($payments),
            'successful' => $successCount,
            'failed' => $errorCount,
            'results' => $results,
            'summary' => $summary,
        ];
    }

    public function consolidate(ProcessedIncomingData $data): mixed
    {
        return DB::transaction(function () use ($data) {
            $state = PaymentConsolidatedIncomingData::from($data->state);
            $expenditures = processor()->resourceResolver($state->paymentIds, 'expenditure');
            $batchDocument = processor()->resourceResolver($data->document_id, 'document');

            if (!$batchDocument) {
                return null;
            }

            foreach ($expenditures as $expenditure) {
                // Prepare Payment Values
                $voucherData = $this->preparePaymentLedger($data, $state, $expenditure);
                $voucher = parent::store($voucherData);
                $resourceDocument = $expenditure->draft->document;
                // $transaction = $this->createFirstTransaction($voucher, $data, $state);

                if (!$voucher || !$resourceDocument) continue;

                // Prepare Payment Voucher Document Values
                $documentCreated = $this->createDocumentForPaymentLedger(
                    $voucher,
                    $expenditure,
                    $resourceDocument,
                    $data,
                    $voucher->total_approved_amount
                );

                if (!$documentCreated) continue;
            }

            $batchDocument->update([
                'status' => 'voucher-generated'
            ]);

            return $batchDocument;
        });
    }

    /**
     * @throws CodeGenerationErrorException
     * @throws Exception
     */
    protected function createFirstTransaction(
        mixed $voucher,
        ProcessedIncomingData $data,
        PaymentConsolidatedIncomingData $state,
    ) {
        $beneficiary = $this->beneficiary($state->entity_id, $data->entity_type);

        if (!$beneficiary) return null;

        $transactionData = [
            'user_id' => Auth::id(),
            'department_id' => Auth::user()->department_id,
            'ledger_id' => $state->ledger_id,
            'chart_of_account_id' => $state->account_code_id,
            'payment_id' => $voucher->id,
            'reference' => $this->transactionRepository->generate('reference', 'trx'),
            'type' => $state->transaction_type_id ?: "debit",
            'amount' => $voucher->total_approved_amount,
            'narration' => '',
            'beneficiary_id' => $beneficiary->id,
            'beneficiary_type' => get_class($beneficiary),
        ];

        return $this->transactionRepository->create($transactionData);
    }

    /**
     * @throws Exception
     */
    protected function createDocumentForPaymentLedger(
        mixed $payment,
        mixed $expenditure,
        mixed $resourceDocument,
        ProcessedIncomingData $data,
        string|float $total_amount
    ): bool {
        $document_type = $this->getDocumentType($data->document_type);
        $document_category = $this->getDocumentCategory($data->document_type, $data->document_category);

        if (!$document_type || !$document_category) {
            return false;
        }

        $params = [
            'workflow_id' => $data->workflow_id,
            'department_id' => $data->department_id,
            'document_action_id' => $data->document_action_id,
            'document_category_id' => $document_category->id,
            'document_type_id' => $document_type->id,
            'linked_document' => $resourceDocument ?? null,
            'relationship_type' => 'payment_voucher'
        ];

        $documentPayload = $this->documentRepository->build(
            $params,
            $payment,
            Auth::user()->department_id,
            "Payment Mandate for {$expenditure->purpose}",
            $payment->narration,
            true,
            $data->trigger_workflow_id,
            $this->workflowArgs(
                PaymentService::class,
                $data->document_action_id ?? 0,
                $payment,
                $total_amount
            )
        );

        $this->documentRepository->create($documentPayload);

        return true;
    }

    private function getDocumentCategory(string $docTypeLabel, string $docCategoryLabel)
    {
        $documentType = $this->getDocumentType($docTypeLabel);

        if (!$documentType) {
            return null;
        }

        return $documentType->categories()->firstWhere('label', $docCategoryLabel);
    }

    /**
     * @throws CodeGenerationErrorException
     */
    private function preparePaymentLedger(
        ProcessedIncomingData $data,
        PaymentConsolidatedIncomingData $state,
        Expenditure $expenditure
    ): array {
        return [
            'code' => $this->generate('code', 'PV'),
            'expenditure_id' => $expenditure->id,
            'payment_batch_id' => $data->document_resource_id,
            'document_draft_id' => $data->document_draft_id,
            'department_id' => $data->department_id,
            'user_id' => Auth::id(),
            'period' => Carbon::parse($state->period),
            'budget_year' => $state->budget_year,
            'resource_id' => $expenditure->expenditureable_id,
            'resource_type' => $expenditure->expenditureable_type,
            'narration' => "Payment Voucher for {$expenditure->purpose}",
            'total_approved_amount' => $expenditure->amount,
            'type' => $data->type,
            'chart_of_account_id' => $state->account_code_id,
            'ledger_id' => $state->ledger_id,
        ];
    }
}
