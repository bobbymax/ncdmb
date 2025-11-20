<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryValuation extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'unit_cost' => 'decimal:4',
        'quantity_on_hand' => 'decimal:4',
        'total_value' => 'decimal:2',
        'valued_at' => 'datetime',
        'meta' => 'array',
    ];

    // Model Relationships or Scope Here...
    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productMeasurement(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductMeasurement::class);
    }

    public function location(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function valuedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'valued_by');
    }
}
