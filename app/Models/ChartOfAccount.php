<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartOfAccount extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function ledgerAccountBalances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LedgerAccountBalance::class, 'chart_of_account_id');
    }

    public function accountPostings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(AccountPosting::class, 'chart_of_account_id');
    }

    public function debitProcessCards(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProcessCard::class, 'debit_account_id');
    }

    public function creditProcessCards(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProcessCard::class, 'credit_account_id');
    }

    public function debitJournalTypes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JournalType::class, 'debit_account_id');
    }

    public function creditJournalTypes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(JournalType::class, 'credit_account_id');
    }
}
