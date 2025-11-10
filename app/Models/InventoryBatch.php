<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryBatch extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'manufactured_at' => 'date',
        'expires_at' => 'date',
        'meta' => 'array',
    ];

    public function supply(): BelongsTo
    {
        return $this->belongsTo(StoreSupply::class, 'store_supply_id');
    }

    public function issueItems(): HasMany
    {
        return $this->hasMany(InventoryIssueItem::class, 'batch_id');
    }
}
