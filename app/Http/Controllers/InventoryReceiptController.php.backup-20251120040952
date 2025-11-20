<?php

namespace App\Http\Controllers;

use App\Http\Resources\InventoryTransactionResource;
use App\Models\StoreSupply;
use App\Services\InventoryTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class InventoryReceiptController extends Controller
{
    use \App\Traits\ApiResponse;

    public function __construct(protected InventoryTransactionService $transactionService)
    {
    }

    public function store(Request $request, StoreSupply $storeSupply)
    {
        $validator = Validator::make($request->all(), [
            'location_id' => 'nullable|exists:inventory_locations,id',
            'quantity' => 'nullable|numeric|min:0.0001',
            'unit_cost' => 'nullable|numeric|min:0',
            'transacted_at' => 'nullable|date',
            'meta' => 'nullable|array',
            'batch.batch_no' => 'nullable|string|max:255',
            'batch.manufactured_at' => 'nullable|date',
            'batch.expires_at' => 'nullable|date|after_or_equal:batch.manufactured_at',
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors(), 'Validation error', 422);
        }

        $user = Auth::user();
        $transaction = $this->transactionService->recordReceipt($storeSupply, $validator->validated(), $user);

        return $this->success(new InventoryTransactionResource($transaction), 'Receipt posted successfully', 201);
    }
}
