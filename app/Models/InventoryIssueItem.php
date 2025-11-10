<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryIssueItem extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'quantity_issued' => 'decimal:4',
        'unit_cost' => 'decimal:4',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(InventoryIssue::class, 'inventory_issue_id');
    }

    public function requisitionItem(): BelongsTo
    {
        return $this->belongsTo(RequisitionItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function measurement(): BelongsTo
    {
        return $this->belongsTo(ProductMeasurement::class, 'product_measurement_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
    }
}
