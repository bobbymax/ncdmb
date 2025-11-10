<?php

namespace App\Services;

use App\Models\InventoryAdjustment;
use App\Models\InventoryBalance;
use App\Models\InventoryIssue;
use App\Models\InventoryIssueItem;
use App\Models\InventoryReturn;
use App\Models\StoreSupply;
use App\Models\User;
use App\Repositories\InventoryBalanceRepository;
use App\Repositories\InventoryBatchRepository;
use App\Repositories\InventoryTransactionRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryTransactionService extends BaseService
{
    public function __construct(
        InventoryTransactionRepository $inventoryTransactionRepository,
        protected InventoryBalanceRepository $balanceRepository,
        protected InventoryBatchRepository $batchRepository,
    ) {
        parent::__construct($inventoryTransactionRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'product_measurement_id' => 'nullable|exists:product_measurements,id',
            'location_id' => 'required|exists:inventory_locations,id',
            'project_contract_id' => 'nullable|exists:project_contracts,id',
            'store_supply_id' => 'nullable|exists:store_supplies,id',
            'inventory_issue_id' => 'nullable|exists:inventory_issues,id',
            'inventory_return_id' => 'nullable|exists:inventory_returns,id',
            'inventory_adjustment_id' => 'nullable|exists:inventory_adjustments,id',
            'type' => 'required|in:receipt,issue,transfer_out,transfer_in,return,adjustment_plus,adjustment_minus,reservation',
            'quantity' => 'required|numeric|min:0.0001',
            'unit_cost' => 'nullable|numeric|min:0',
            'value' => 'nullable|numeric|min:0',
            'transacted_at' => 'nullable|date',
            'meta' => 'nullable|array',
        ];
    }

    public function recordReceipt(StoreSupply $supply, array $payload, User $user)
    {
        $quantity = (float) ($payload['quantity'] ?? $supply->quantity ?? 0);
        if ($quantity <= 0) {
            throw new RuntimeException('Receipt quantity must be greater than zero.');
        }

        $unitCost = (float) ($payload['unit_cost'] ?? $supply->unit_price ?? 0);
        $locationId = $payload['location_id'] ?? $supply->inventory_location_id;

        if (!$locationId) {
            throw new RuntimeException('A valid inventory location is required for this receipt.');
        }

        $measurementId = $payload['product_measurement_id'] ?? $supply->product_measurement_id;

        return DB::transaction(function () use ($supply, $payload, $user, $quantity, $unitCost, $locationId, $measurementId) {
            $balance = $this->balanceRepository->upsert($supply->product_id, $measurementId, $locationId, [
                'unit_cost' => $unitCost,
            ]);

            $this->applyBalanceDelta($balance, $quantity, 0, $unitCost);

            $transaction = $this->repository->create([
                'product_id' => $supply->product_id,
                'product_measurement_id' => $measurementId,
                'location_id' => $locationId,
                'project_contract_id' => $supply->project_contract_id,
                'store_supply_id' => $supply->id,
                'type' => 'receipt',
                'quantity' => $quantity,
                'unit_cost' => $unitCost,
                'value' => $unitCost ? round($unitCost * $quantity, 4) : null,
                'meta' => Arr::get($payload, 'meta', []),
                'performed_by' => $user->getKey(),
                'transacted_at' => Arr::get($payload, 'transacted_at'),
            ]);

            if (!empty($payload['batch'])) {
                $this->batchRepository->create([
                    'store_supply_id' => $supply->id,
                    'batch_no' => Arr::get($payload['batch'], 'batch_no'),
                    'manufactured_at' => Arr::get($payload['batch'], 'manufactured_at'),
                    'expires_at' => Arr::get($payload['batch'], 'expires_at'),
                    'meta' => Arr::except($payload['batch'], ['batch_no', 'manufactured_at', 'expires_at']),
                ]);
            }

            $supply->forceFill([
                'inventory_location_id' => $locationId,
                'received_at' => $payload['transacted_at'] ?? now(),
                'status' => $payload['status'] ?? 'fulfilled',
            ])->save();

            return $transaction->load(['product', 'location']);
        });
    }

    public function recordIssue(InventoryIssue $issue, InventoryIssueItem $issueItem, float $quantity, array $meta = []): mixed
    {
        if ($quantity <= 0) {
            throw new RuntimeException('Issued quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($issue, $issueItem, $quantity, $meta) {
            $balance = $this->balanceRepository->upsert(
                $issueItem->product_id,
                $issueItem->product_measurement_id,
                $issue->from_location_id
            );

            if ((float) $balance->available < $quantity) {
                throw new RuntimeException("Insufficient stock available for product {$issueItem->product_id} at this location.");
            }

            $reservedDelta = 0;
            if ((float) $balance->reserved > 0) {
                $reservedDelta = -min((float) $balance->reserved, $quantity);
            }

            $this->applyBalanceDelta($balance, -$quantity, $reservedDelta);

            return $this->repository->create([
                'product_id' => $issueItem->product_id,
                'product_measurement_id' => $issueItem->product_measurement_id,
                'location_id' => $issue->from_location_id,
                'inventory_issue_id' => $issue->getKey(),
                'type' => 'issue',
                'quantity' => $quantity,
                'unit_cost' => (float) ($meta['unit_cost'] ?? $balance->unit_cost),
                'value' => isset($meta['unit_cost']) ? round($meta['unit_cost'] * $quantity, 4) : null,
                'meta' => Arr::except($meta, ['unit_cost']),
                'performed_by' => $issue->issued_by,
                'transacted_at' => $meta['transacted_at'] ?? $issue->issued_at,
            ])->load('product');
        });
    }

    public function recordReturn(InventoryReturn $return, array $payload, User $user)
    {
        $quantity = (float) Arr::get($payload, 'quantity');
        if ($quantity <= 0) {
            throw new RuntimeException('Return quantity must be greater than zero.');
        }

        $productId = Arr::get($payload, 'product_id');
        $measurementId = Arr::get($payload, 'product_measurement_id');
        $locationId = $return->location_id;

        return DB::transaction(function () use ($return, $payload, $user, $quantity, $productId, $measurementId) {
            $balance = $this->balanceRepository->upsert($productId, $measurementId, $return->location_id);

            $this->applyBalanceDelta($balance, $quantity, 0, Arr::get($payload, 'unit_cost'));

            return $this->repository->create([
                'product_id' => $productId,
                'product_measurement_id' => $measurementId,
                'location_id' => $locationId,
                'inventory_return_id' => $return->getKey(),
                'type' => 'return',
                'quantity' => $quantity,
                'unit_cost' => (float) Arr::get($payload, 'unit_cost', $balance->unit_cost),
                'value' => Arr::get($payload, 'unit_cost') ? round($quantity * Arr::get($payload, 'unit_cost'), 4) : null,
                'meta' => Arr::except($payload, ['product_id', 'product_measurement_id', 'quantity', 'unit_cost']),
                'performed_by' => $user->getKey(),
                'transacted_at' => Arr::get($payload, 'transacted_at') ?? $return->returned_at,
            ])->load('product');
        });
    }

    public function recordAdjustment(InventoryAdjustment $adjustment, array $payload, User $user)
    {
        $quantity = (float) Arr::get($payload, 'quantity');
        if ($quantity <= 0) {
            throw new RuntimeException('Adjustment quantity must be greater than zero.');
        }

        $direction = Arr::get($payload, 'direction', 'plus');
        $productId = Arr::get($payload, 'product_id');
        $measurementId = Arr::get($payload, 'product_measurement_id');

        $multiplier = $direction === 'minus' ? -1 : 1;

        return DB::transaction(function () use ($adjustment, $payload, $user, $quantity, $productId, $measurementId, $multiplier) {
            $balance = $this->balanceRepository->upsert($productId, $measurementId, $adjustment->location_id);

            if ($multiplier < 0 && (float) $balance->available < $quantity) {
                throw new RuntimeException('Cannot adjust more stock than available.');
            }

            $this->applyBalanceDelta($balance, $quantity * $multiplier, 0, Arr::get($payload, 'unit_cost'));

            $type = $multiplier > 0 ? 'adjustment_plus' : 'adjustment_minus';

            return $this->repository->create([
                'product_id' => $productId,
                'product_measurement_id' => $measurementId,
                'location_id' => $adjustment->location_id,
                'inventory_adjustment_id' => $adjustment->getKey(),
                'type' => $type,
                'quantity' => $quantity,
                'unit_cost' => (float) Arr::get($payload, 'unit_cost', $balance->unit_cost),
                'value' => Arr::get($payload, 'unit_cost') ? round($quantity * Arr::get($payload, 'unit_cost'), 4) : null,
                'meta' => Arr::except($payload, ['product_id', 'product_measurement_id', 'quantity', 'unit_cost', 'direction']),
                'performed_by' => $user->getKey(),
                'transacted_at' => Arr::get($payload, 'transacted_at') ?? $adjustment->adjusted_at,
            ])->load('product');
        });
    }

    protected function applyBalanceDelta(InventoryBalance $balance, float $onHandDelta, float $reservedDelta = 0, ?float $unitCost = null): InventoryBalance
    {
        $onHand = round((float) $balance->on_hand + $onHandDelta, 4);
        $reserved = round((float) $balance->reserved + $reservedDelta, 4);

        if ($onHand < -0.0001) {
            throw new RuntimeException('Resulting stock balance would be negative.');
        }

        if ($reserved < 0) {
            $reserved = 0;
        }

        $balance->on_hand = $onHand;
        $balance->reserved = $reserved;
        $balance->available = round($onHand - $reserved, 4);

        if ($unitCost !== null) {
            $balance->unit_cost = $unitCost;
        }

        $balance->last_movement_at = now();
        $balance->save();

        return $balance->refresh();
    }
}
