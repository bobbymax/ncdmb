<?php

namespace App\Services;

use App\Handlers\CodeGenerationErrorException;
use App\Models\Document;
use App\Models\Expenditure;
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

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $expenditures = $this->expenditureRepository->whereIn('id', $data['paymentIds']);
            $batchDocument = $this->documentRepository->find($data['document_id']);

            if (!$batchDocument) {
                return null;
            }

            foreach ($expenditures as $expenditure) {
                // Prepare Payment Values
                $voucherData = $this->preparePaymentLedger($data, $expenditure, $batchDocument);
                $voucher = parent::store($voucherData);

                if (!$voucher) continue;

                // Get Expenditure Document
                $resourceDocument = optional(optional($expenditure->draft)->document);

                // Build & Store First DEBIT/CREDIT Transaction
                $transaction = $this->createFirstTransaction($voucher, $data);

                if (!$transaction) continue;

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

            return true;
        });
    }

    /**
     * @throws CodeGenerationErrorException
     * @throws Exception
     */
    protected function createFirstTransaction(
        mixed $voucher,
        array $data
    ) {
        $beneficiary = $this->beneficiary($data['entity_id'], $data['entity_type']);

        if (!$beneficiary) return null;

        $transactionData = [
            'user_id' => Auth::id(),
            'department_id' => Auth::user()->department_id,
            'ledger_id' => $voucher['ledger_id'],
            'chart_of_account_id' => $data['account_code_id'],
            'payment_id' => $voucher->id,
            'reference' => $this->generate('reference', 'trx'),
            'type' => $data['transaction_type_id'],
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
        array $data,
        string|float $total_amount
    ): bool {
        $document_type = $this->getDocumentType($data['document_type']);
        $document_category = $this->getDocumentCategory($data['document_type'], $data['document_category']);

        if (!$document_type || !$document_category) {
            return false;
        }

        $documentPayload = $this->documentRepository->build(
            [
                ...$data,
                'document_category_id' => $document_category->id,
                'document_type_id' => $document_type->id,
                'linked_document' => $resourceDocument ?? null,
                'relationship_type' => 'payment_voucher'
            ],
            $payment,
            Auth::user()->department_id,
            "Payment Mandate for {$expenditure->purpose}",
            $payment->narration,
            true,
            $data['trigger_workflow_id'],
            $this->workflowArgs(
                PaymentService::class,
                $data['document_action_id'] ?? 0,
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
        array $data,
        Expenditure $expenditure,
        Document $document
    ): array {
        return [
            'code' => $this->generate('code', 'PV'),
            'expenditure_id' => $expenditure->id,
            'payment_batch_id' => $data['payment_batch_id'],
            'document_draft_id' => $data['document_draft_id'],
            'department_id' => $data['department_id'],
            'user_id' => Auth::id(),
            'period' => Carbon::parse($data['period']),
            'budget_year' => $data['budget_year'],
            'resource_id' => $document->documentable_id,
            'resource_type' => $document->documentable_type,
            'narration' => "Payment Voucher for {$expenditure->purpose}",
            'total_approved_amount' => $expenditure->amount,
            'type' => $data['type'],
        ];
    }
}
