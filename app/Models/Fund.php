<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fund extends Model
{
    use HasFactory;

    protected $guarded = [''];

    // Model Relationships or Scope Here...
    public function batches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentBatch::class);
    }

    public function boardProjects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BoardProject::class);
    }

    public function expenditures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expenditure::class, 'fund_id');
    }

    public function flightReservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FlightReservation::class);
    }

    public function mandates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Mandate::class);
    }

    public function meetings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Meeting::class, 'fund_id');
    }

    public function subBudgetHead(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(SubBudgetHead::class, 'sub_budget_head_id');
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function budgetCode(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(BudgetCode::class, 'budget_code_id');
    }

    public function hotelReservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HotelReservation::class);
    }

    public function reservedFunds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reserve::class);
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
