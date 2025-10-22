<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function groups(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    public function journalTypes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JournalType::class);
    }

    public function processCards(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProcessCard::class);
    }

    public function ledgerAccountBalances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LedgerAccountBalance::class, 'ledger_id');
    }

    public function reconciliations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reconciliation::class, 'ledger_id');
    }

    public function accountPostings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AccountPosting::class, 'ledger_id');
    }
}
