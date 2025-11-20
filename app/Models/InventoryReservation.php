<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryReservation extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'quantity_reserved' => 'decimal:4',
        'reserved_until' => 'datetime',
    ];

    // Model Relationships or Scope Here...
    public function requisitionItem(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(RequisitionItem::class);
    }

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

    public function reservedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'reserved_by');
    }
}
