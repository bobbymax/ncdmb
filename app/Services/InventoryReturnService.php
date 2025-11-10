<?php

namespace App\Services;

use App\Models\InventoryReturn;
use App\Repositories\InventoryReturnRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryReturnService extends BaseService
{
    public function __construct(
        InventoryReturnRepository $inventoryReturnRepository,
        protected InventoryTransactionService $transactionService,
    ) {
        parent::__construct($inventoryReturnRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'inventory_issue_id' => 'nullable|exists:inventory_issues,id',
            'store_supply_id' => 'nullable|exists:store_supplies,id',
            'location_id' => 'required|exists:inventory_locations,id',
            'type' => 'required|in:to_supplier,from_project,internal',
            'returned_at' => 'nullable|date',
            'reason' => 'nullable|string',
            'product_id' => 'required|exists:products,id',
            'product_measurement_id' => 'nullable|exists:product_measurements,id',
            'quantity' => 'required|numeric|min:0.0001',
            'unit_cost' => 'nullable|numeric|min:0',
        ];
    }

    public function store(array $data)
    {
        $user = Auth::user();
        if (!$user) {
            throw new RuntimeException('Authenticated user is required to create a return.');
        }

        return DB::transaction(function () use ($data, $user) {
            /** @var InventoryReturn $return */
            $return = $this->repository->create([
                'inventory_issue_id' => $data['inventory_issue_id'] ?? null,
                'store_supply_id' => $data['store_supply_id'] ?? null,
                'type' => $data['type'],
                'processed_by' => $user->getKey(),
                'location_id' => $data['location_id'],
                'returned_at' => $data['returned_at'] ?? now(),
                'reason' => $data['reason'] ?? null,
            ]);

            $transaction = $this->transactionService->recordReturn($return, Arr::only($data, [
                'product_id',
                'product_measurement_id',
                'quantity',
                'unit_cost',
                'transacted_at',
            ]), $user);

            return $return->load(['transactions', 'issue', 'supply'])->setRelation('transactions', collect([$transaction]));
        });
    }
}
