<?php

namespace App\Services;


use App\DTO\ProcessedIncomingData;
use App\Handlers\DataNotFound;
use App\Repositories\ExpenseRepository;

class ExpenseService extends BaseService
{
    public function __construct(ExpenseRepository $expenseRepository)
    {
        parent::__construct($expenseRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'claim_id' => 'required|integer|exists:claims,id',
            'identifier' => 'required|string|unique:expenses,identifier',
            'parent_id' => 'required|integer|exists:allowances,id',
            'allowance_id' => 'required|integer|exists:allowances,id',
            'trip_id' => 'sometimes|integer|min:0',
            'remuneration_id' => 'required|integer|exists:remunerations,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'no_of_days' => 'required|integer|min:1',
            'total_amount_spent' => 'required|numeric|min:1',
            'unit_price' => 'sometimes|numeric|min:1',
            'total_distance_covered' => 'sometimes|numeric|min:0',
            'description' => 'required|string|min:3',
        ];
    }

    /**
     * @throws DataNotFound
     */
    public function processExpenses(ProcessedIncomingData $processedIncomingData): bool
    {
        $document = processor()->resourceResolver($processedIncomingData->document_id, 'document');
        $payment = processor()->resourceResolver($processedIncomingData->document_resource_id, 'payment');
        $claim = processor()->resourceResolver($processedIncomingData->state['claim_id'], 'claim');
        $editor = processor()->resourceResolver($processedIncomingData->state['editor_id'], 'resourceeditor');

        if (!$document || !$payment || !$claim || !$editor) {
            return false;
        }

        $expenses = collect($processedIncomingData->resources)->map(function ($item) {
            return $item['raw'] ?? [];
        });

        foreach ($expenses as $obj) {
            $expense = $this->show($obj['id']);

            if ($expense) {
                parent::update($expense->id, $obj);
            }
        }

        $claimSum = array_reduce($expenses->toArray(), function ($carry, $item) use ($editor) {
            $key = $editor->resource_column_name;
            return $carry + ($item[$key] ?? 0);
        }, 0);

        $document->update([
            'status' => $processedIncomingData->status,
            'document_action_id' => $processedIncomingData->document_action_id,
        ]);

        $payment->update([
            'total_amount_paid' => $processedIncomingData->status === "audited" ? $claimSum : 0
        ]);

        $payment->expenditure->update([
            'status' => $processedIncomingData->status,
        ]);

        $claim->update([
            'status' => $processedIncomingData->status,
            'total_amount_approved' => $processedIncomingData->status === "audited" ? $claimSum : 0,
        ]);

        return true;
    }
}
