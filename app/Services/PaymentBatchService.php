<?php

namespace App\Services;

use App\Engine\ControlEngine;
use App\Handlers\CodeGenerationErrorException;
use App\Repositories\DocumentDraftRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\ExpenditureRepository;
use App\Repositories\PaymentBatchRepository;
use App\Traits\DocumentFlow;
use App\Traits\ServiceAction;
use Exception;
use Illuminate\Support\Facades\DB;

class PaymentBatchService extends BaseService
{
    use DocumentFlow, ServiceAction;
    protected ExpenditureRepository $expenditureRepository;
    protected DocumentDraftRepository $documentDraftRepository;
    protected DocumentRepository $documentRepository;
    protected ControlEngine $engine;

    public function __construct(
        PaymentBatchRepository $paymentBatchRepository,
        ExpenditureRepository $expenditureRepository,
        DocumentDraftRepository $documentDraftRepository,
        DocumentRepository $documentRepository,
        ControlEngine $engine,
    ) {
        parent::__construct($paymentBatchRepository);
        $this->expenditureRepository = $expenditureRepository;
        $this->documentDraftRepository = $documentDraftRepository;
        $this->documentRepository = $documentRepository;
        $this->engine = $engine;
    }

    public function rules($action = "store"): array
    {
        return [
            'fund_id' => 'required|integer|exists:funds,id',
            'user_id' => 'required|integer|exists:users,id',
            'department_id' => 'required|integer|exists:departments,id',
            'workflow_id' => 'required|integer|exists:workflows,id',
            'document_category_id' => 'required|integer|exists:document_categories,id',
            'document_type_id' => 'required|integer|exists:document_types,id',
            'budget_year' => 'required|integer|digits:4',
            'type' => 'required|string|in:staff,third-party',
            'expenditures' => 'required|array',
            'expenditures.*.id' => 'required|integer|exists:expenditures,id',
            'expenditures.*.trackable_draft_id' => 'required|integer|exists:document_drafts,id',
        ];
    }

    protected function getPrefix(string $type): string
    {
        return $type === 'third-party' ? 'TPP' : 'SP';
    }

    /**
     * @throws CodeGenerationErrorException
     * @throws Exception
     */
    protected function createPaymentBatch(array $data)
    {
        $prefix = $this->getPrefix($data['type']);

        return parent::store([
            ...$data,
            'code' => $this->generate('code', $prefix),
            'status' => "pending"
        ]);
    }

    /**
     * @throws \Exception
     */
    protected function createDocumentForBatch($batch, array $data, $total_amount)
    {
        $documentData = $this->documentRepository->build(
            [
                ...$data,
                'document_action_id' => $this->getCreationDocumentAction()?->id
            ],
            $batch,
            $data['department_id'],
            "Batch Payment: {$batch->code}",
            "Batch Payment Document Generated!!",
            true,
            null,
            $this->workflowArgs(
                PaymentBatchService::class,
                $this->getCreationDocumentAction()?->id,
                $batch,
                $total_amount,
            )
        );

        return $this->documentRepository->create($documentData);
    }

    protected function linkResourceDocumentsToBatch(array $expenditures, $document): void
    {
        $expenditureIds = $this->isolateKeys($expenditures, 'id');
        $expenditureCollection = $this->expenditureRepository->whereIn('id', $expenditureIds);

        foreach ($expenditureCollection as $expenditure) {
            $expenditureDocument = $expenditure->draft->document;
            if (!$expenditureDocument) continue;
            $this->documentRepository
                ->linkRelatedDocument(
                    $document,
                    $expenditureDocument,
                    "payment_batch"
                );
        }
    }

    protected function attachExpendituresToDocument(array $expenditures, $document): void
    {
        foreach ($expenditures as $value) {
            $expenditure = $this->expenditureRepository->find($value['id']);
            $draft = $this->documentDraftRepository->find($value['trackable_draft_id']);
            $draftDocument = $draft->document;

            if ($expenditure && $draft && $draftDocument) {
                $expenditure->update([
                    'document_reference_id' => $document->id,
                    'status' => 'batched'
                ]);

                $draft->update([
                    'sub_document_reference_id' => $document->id,
                    'status' => 'batched'
                ]);

                $draftDocument->update([
                    'document_reference_id' => $document->id,
                ]);
            }
        }
    }

    public function store(array $data)
    {
        return  DB::transaction(function () use ($data) {
            $batch = $this->createPaymentBatch($data);

            if (!$batch) {
                return null;
            }

            $total_amount = array_reduce($data['expenditures'], function ($carry, $expenditure) {
                return $carry + ($expenditure->amount ?? 0);
            }, 0);

            $document = $this->createDocumentForBatch($batch, $data, $total_amount);
            $this->attachExpendituresToDocument($data['expenditures'], $document);
            $this->linkResourceDocumentsToBatch($data['expenditures'], $document);

            return $batch;
        });
    }
}
