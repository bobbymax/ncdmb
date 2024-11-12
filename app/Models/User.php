<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function advances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TouringAdvance::class);
    }

    public function batches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PaymentBatch::class, 'batch_id');
    }
    public function boardProjects(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BoardProject::class);
    }

    public function budgetProjectActivities(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(BudgetProjectActivity::class);
    }

    public function contracts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProjectContract::class);
    }

    public function expenditures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expenditure::class, 'user_id');
    }

    public function mandatesGenerated(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Mandate::class);
    }

    public function role(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function treatedItineraries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FlightItinerary::class, 'user_id');
    }

    public function flightItineraries(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(FlightItinerary::class, FlightItinerary::class);
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function flightReservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FlightReservation::class);
    }

    public function gradeLevel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function location(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function payments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Expenditure::class, 'staff_id');
    }

    public function requisitions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Requisition::class);
    }

    public function reservedFunds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reserve::class, 'user_id');
    }

    public function handleReserves(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reserve::class, 'staff_id');
    }

    public function supplies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StoreSupply::class);
    }

    public function uploads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Upload::class);
    }

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function paymentsReceived(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Transaction::class, 'staff_id');
    }
}
