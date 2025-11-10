<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'value' => 'decimal:4',
        'meta' => 'array',
        'transacted_at' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function measurement(): BelongsTo
    {
        return $this->belongsTo(ProductMeasurement::class, 'product_measurement_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ProjectContract::class, 'project_contract_id');
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(StoreSupply::class, 'store_supply_id');
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(InventoryIssue::class, 'inventory_issue_id');
    }

    public function returnRecord(): BelongsTo
    {
        return $this->belongsTo(InventoryReturn::class, 'inventory_return_id');
    }

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(InventoryAdjustment::class, 'inventory_adjustment_id');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
