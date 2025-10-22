<?php

namespace App\Services;

use App\Engine\ControlEngine;
use App\Handlers\CodeGenerationErrorException;
use App\Handlers\DataNotFound;
use App\Models\Document;
use App\Repositories\DocumentDraftRepository;
use App\Repositories\DocumentRepository;
use App\Repositories\ExpenditureRepository;
use App\Repositories\PaymentBatchRepository;
use App\Traits\DocumentFlow;
use App\Traits\ServiceAction;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function bindRelatedDocuments(
        Document $document,
        mixed $resource,
        string $status = "processing",
    ): void {
        $paymentBatch = $resource;

        foreach ($paymentBatch->expenditures as $expenditure) {
            $this->documentRepository->linkRelatedDocument(
                $document,
                $expenditure->document,
                'white-form',
                $status
            );
        }
    }

    public function resolveDocumentAmount(int $resourceId)
    {
        $raw = $this->repository->find($resourceId);

        if (!$raw || !$raw->document) {
            return null;
        }

        $raw->document->approved_amount = $raw->expenditures()->sum('amount');
        $raw->document->save();

        return $raw;
    }

    /**
     * @throws CodeGenerationErrorException
     * @throws DataNotFound
     * @throws Exception
     */
    public function buildDocumentFromTemplate(array $data, bool $isUpdate = false)
    {
        $resolved = $this->resolveContent($data);

        $existingId = (int) data_get($resolved, 'existing_resource_id', 0);

        $batch = [
            'user_id' => Auth::id(),
            'department_id' => $data['department_owner']['value'] ?? Auth::user()->department_id,
            'fund_id' => $resolved['fund']['value'] ?? 0,
            'description' => $resolved['contents']['purpose'] ?? '',
            'budget_year' => $resolved['budget_year'],
            'type' => $resolved['type'],
        ];

        $paymentBatch = null;

        if ($existingId > 0 && $isUpdate) {
            $paymentBatch = parent::update($existingId, $batch);
        } else {
            $batch['code'] = $this->repository->generate('code', $this->getPrefix($resolved['type']));
            $batch['status'] = 'generated';
            $paymentBatch = parent::store($batch);
        }

        if (!$paymentBatch) {
            return null;
        }

        $documents = (array) data_get($resolved, 'contents.documents', []);

        if (!empty($documents)) {
            foreach ($documents as $d) {
                // variation_amount is acting as the admin_fee_amount
                $expData = [
                    'fund_id' => $paymentBatch->fund_id,
                    'document_id' => $d['id'],
                    'payment_batch_id' => $paymentBatch->id,
                    'purpose' => $d['title'],
                    'amount' => (float) $d['approved_amount'],
                    'sub_total_amount' => (float) $d['sub_total_amount'],
                    'admin_fee_amount' => (float) $d['admin_fee_amount'],
                    'vat_amount' => (float) $d['vat_amount'],
                    'expense_type' => $paymentBatch->type,
                    'budget_year' => $paymentBatch->budget_year,
                ];


                $expenditure = $this->expenditureRepository->getRecordByColumn('code', $d['identifier']);

                if ($expenditure) {
                    $this->expenditureRepository->update($expenditure->id, $expData);
                } else {
                    $expData['code'] = 'tRx_' . $d['identifier'];
                    $this->expenditureRepository->create($expData);
                }

                $this->documentRepository->update($d['id'], [
                    'status' => 'batched'
                ]);
            }
        }



        return $paymentBatch;
    }

    public function resolveContent(array $data): array
    {
        $contents = [
            'purpose' => '',
            'documents' => null,
            'total_amount' => 0,
            'no_of_payments' => 0
        ];

        foreach ($data['content'] as $item) {
            if (isset($item['content']['payment_batch'])) {
                $contents['purpose'] = $item['content']['payment_batch']['purpose'];
                $contents['documents'] = $item['content']['payment_batch']['documents'];
                $contents['total_amount'] = (float) $item['content']['payment_batch']['total_amount'];
                $contents['no_of_payments'] = (int) $item['content']['payment_batch']['no_of_payments'];
            }
        }

        return [
            'category' => $data['category'],
            'existing_resource_id' => $data['existing_resource_id'],
            'contents' => $contents,
            'type' => $data['type'],
            'budget_year' => $data['budget_year'],
            'fund' => $data['fund'],
            'existing_document_id' => $data['existing_document_id']
        ];
    }

    /**
     * @throws CodeGenerationErrorException
     * @throws Exception
     */
    protected function createPaymentBatch(array $data)
    {
        $prefix = $this->getPrefix($data['category']['type']);

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
