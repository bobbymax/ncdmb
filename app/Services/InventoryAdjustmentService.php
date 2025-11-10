<?php

namespace App\Services;

use App\Repositories\InventoryAdjustmentRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryAdjustmentService extends BaseService
{
    public function __construct(
        InventoryAdjustmentRepository $inventoryAdjustmentRepository,
        protected InventoryTransactionService $transactionService,
    ) {
        parent::__construct($inventoryAdjustmentRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'location_id' => 'required|exists:inventory_locations,id',
            'performed_by' => 'nullable|exists:users,id',
            'reason' => 'required|in:cycle_count,damage,shrinkage,rebalance,other',
            'notes' => 'nullable|string',
            'adjusted_at' => 'nullable|date',
            'lines' => 'required|array|min:1',
            'lines.*.product_id' => 'required|exists:products,id',
            'lines.*.product_measurement_id' => 'nullable|exists:product_measurements,id',
            'lines.*.quantity' => 'required|numeric|min:0.0001',
            'lines.*.direction' => 'nullable|in:plus,minus',
            'lines.*.unit_cost' => 'nullable|numeric|min:0',
        ];
    }

    public function store(array $data)
    {
        $user = Auth::user();
        if (!$user) {
            throw new RuntimeException('Authenticated user is required to create an adjustment.');
        }

        return DB::transaction(function () use ($data, $user) {
            $adjustment = $this->repository->create([
                'location_id' => $data['location_id'],
                'performed_by' => $user->getKey(),
                'reason' => $data['reason'] ?? 'other',
                'notes' => $data['notes'] ?? null,
                'adjusted_at' => $data['adjusted_at'] ?? now(),
                'meta' => Arr::except($data, ['lines']),
            ]);

            $transactions = [];
            foreach ($data['lines'] as $line) {
                $transactions[] = $this->transactionService->recordAdjustment($adjustment, [
                    'product_id' => $line['product_id'],
                    'product_measurement_id' => $line['product_measurement_id'] ?? null,
                    'quantity' => $line['quantity'],
                    'direction' => $line['direction'] ?? 'plus',
                    'unit_cost' => $line['unit_cost'] ?? null,
                ], $user);
            }

            return $adjustment->setRelation('transactions', collect($transactions));
        });
    }
}
