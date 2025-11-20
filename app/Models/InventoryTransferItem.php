<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryTransferItem extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'quantity_transferred' => 'decimal:4',
        'quantity_received' => 'decimal:4',
        'unit_cost' => 'decimal:4',
    ];

    // Model Relationships or Scope Here...
    public function inventoryTransfer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InventoryTransfer::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productMeasurement(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductMeasurement::class);
    }

    public function batch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InventoryBatch::class, 'batch_id');
    }
}
