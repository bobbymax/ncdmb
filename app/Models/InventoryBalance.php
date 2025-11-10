<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryBalance extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'on_hand' => 'decimal:4',
        'reserved' => 'decimal:4',
        'available' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'last_movement_at' => 'datetime',
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

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'product_id', 'product_id')
            ->where('inventory_transactions.product_measurement_id', $this->product_measurement_id)
            ->where('inventory_transactions.location_id', $this->location_id);
    }
}
