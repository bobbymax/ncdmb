<?php

namespace App\Services;

use App\DTO\ProcessedIncomingData;
use App\Repositories\TransactionRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionService extends BaseService
{
    public function __construct(TransactionRepository $transactionRepository)
    {
        parent::__construct($transactionRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }

    /**
     * @throws \Exception
     */
    public function settleTransactions(ProcessedIncomingData $processedIncomingData): bool
    {
        return DB::transaction(function () use ($processedIncomingData) {
            $document = processor()->resourceResolver($processedIncomingData->document_id, 'document');
            $payment = processor()->resourceResolver($processedIncomingData->document_resource_id, 'payment');

            if (!$document || !$payment) {
                return false;
            }

            // Build enriched transactions
            $transactions = collect($processedIncomingData->resources)->map(function ($item) use ($processedIncomingData, $payment) {
                $raw = $item['raw'] ?? [];

                unset($raw['id']); // Safe even if not set

                return array_merge($raw, [
                    'user_id' => $processedIncomingData->user_id,
                    'department_id' => $processedIncomingData->department_id,
                    'payment_id' => $payment->id,
                    'ledger_id' => $processedIncomingData->state['ledger_id'] ?? 0,
                ]);
            });

            // Store transactions
            foreach ($transactions as $transaction) {
                if ($processedIncomingData->mode === "update") {
                    $trx = $this->getRecordByColumn('reference', $transaction['reference']);

                    if ($trx) {
                        parent::update($trx->id, $transaction);
                    }
                } else {
                    parent::store($transaction);
                }
            }

            // Update related records
            $this->updateRelatedStatuses($document, $payment, $processedIncomingData->document_action_id, $processedIncomingData->status);

            return true;
        });
    }

    // DRY helper method for status updates
    protected function updateRelatedStatuses($document, $payment, $actionId, $status): void
    {
        $document->update([
            'document_action_id' => $actionId,
            'status' => $status,
        ]);

        $payment->expenditure->update([
            'status' => $status,
        ]);

        $payment->expenditure->expenditureable->update([
            'status' => $status,
        ]);
    }
}
