<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryReturn extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'returned_at' => 'datetime',
        'meta' => 'array',
    ];

    public function issue(): BelongsTo
    {
        return $this->belongsTo(InventoryIssue::class, 'inventory_issue_id');
    }

    public function supply(): BelongsTo
    {
        return $this->belongsTo(StoreSupply::class, 'store_supply_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class, 'inventory_return_id');
    }
}
