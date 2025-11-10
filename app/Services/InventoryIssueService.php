<?php

namespace App\Services;

use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Repositories\InventoryIssueItemRepository;
use App\Repositories\InventoryIssueRepository;
use App\Repositories\InventoryBalanceRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryIssueService extends BaseService
{
    public function __construct(
        InventoryIssueRepository $inventoryIssueRepository,
        protected InventoryIssueItemRepository $issueItemRepository,
        protected InventoryTransactionService $transactionService,
        protected InventoryBalanceRepository $balanceRepository,
    ) {
        parent::__construct($inventoryIssueRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'requisition_id' => 'required|exists:requisitions,id',
            'issued_to' => 'nullable|exists:users,id',
            'from_location_id' => 'required|exists:inventory_locations,id',
            'issued_at' => 'nullable|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.requisition_item_id' => 'required|exists:requisition_items,id',
            'items.*.product_measurement_id' => 'nullable|exists:product_measurements,id',
            'items.*.quantity' => 'required|numeric|min:0.0001',
            'items.*.unit_cost' => 'nullable|numeric|min:0',
            'items.*.batch_id' => 'nullable|exists:inventory_batches,id',
        ];
    }

    public function store(array $data)
    {
        $user = Auth::user();
        if (!$user) {
            throw new RuntimeException('Authenticated user is required to post an issue.');
        }

        $requisition = Requisition::with('items')->findOrFail($data['requisition_id']);

        return DB::transaction(function () use ($data, $user, $requisition) {
            $issue = $this->repository->create([
                'requisition_id' => $requisition->getKey(),
                'issued_by' => $user->getKey(),
                'issued_to' => $data['issued_to'] ?? $requisition->staff_id ?: $requisition->user_id,
                'from_location_id' => $data['from_location_id'],
                'reference' => $this->repository->generate('reference', 'ISU-'),
                'issued_at' => $data['issued_at'] ?? now(),
                'remarks' => $data['remarks'] ?? null,
            ]);

            foreach ($data['items'] as $line) {
                /** @var RequisitionItem $requisitionItem */
                $requisitionItem = $requisition->items()
                    ->whereKey($line['requisition_item_id'])
                    ->lockForUpdate()
                    ->first();

                if (!$requisitionItem) {
                    throw new RuntimeException('Requisition item could not be found or is not linked to the requisition.');
                }

                $quantity = (float) $line['quantity'];
                if ($quantity <= 0) {
                    throw new RuntimeException('Issue quantity must be greater than zero.');
                }

                $measurementId = $line['product_measurement_id'] ?? null;

                $balance = $this->balanceRepository->upsert(
                    $requisitionItem->product_id,
                    $measurementId,
                    $data['from_location_id']
                );

                if ((float) $balance->available < $quantity) {
                    throw new RuntimeException("Insufficient stock available to issue product {$requisitionItem->product_id}.");
                }

                $issueItem = $this->issueItemRepository->create([
                    'inventory_issue_id' => $issue->getKey(),
                    'requisition_item_id' => $requisitionItem->getKey(),
                    'product_id' => $requisitionItem->product_id,
                    'product_measurement_id' => $measurementId,
                    'quantity_issued' => $quantity,
                    'unit_cost' => $line['unit_cost'] ?? $balance->unit_cost,
                    'batch_id' => $line['batch_id'] ?? null,
                ]);

                $this->transactionService->recordIssue($issue, $issueItem, $quantity, [
                    'unit_cost' => $issueItem->unit_cost,
                    'transacted_at' => $data['issued_at'] ?? now(),
                ]);

                $requisitionItem->increment('quantity_issued', $quantity);
                $requisitionItem->decrement('quantity_reserved', min($requisitionItem->quantity_reserved, $quantity));
                $requisitionItem->stock_balance = max(0, $balance->available);
                $requisitionItem->save();
            }

            $this->syncRequisitionStatus($requisition->fresh(['items']));

            return $issue->load(['items.product', 'requisition']);
        });
    }

    protected function syncRequisitionStatus(Requisition $requisition): void
    {
        $remaining = $requisition->items->filter(function (RequisitionItem $item) {
            return (float) $item->quantity_requested > (float) $item->quantity_issued;
        })->isNotEmpty();

        $status = $remaining ? 'in-progress' : 'approved';
        $requisition->status = $status;
        if ($status === 'approved') {
            $requisition->date_approved_or_rejected = now();
        }
        $requisition->save();
    }
}
