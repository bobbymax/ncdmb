<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryReceipt extends Model
{
    use HasFactory;

    protected $guarded = [''];

    protected $casts = [
        'received_at' => 'datetime',
        'meta' => 'array',
    ];

    // Model Relationships or Scope Here...
    public function storeSupply(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(StoreSupply::class);
    }

    public function receivedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function location(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InventoryReceiptItem::class);
    }
}
