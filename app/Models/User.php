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
    protected $guarded = [''];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
        ];
    }

    /**
     * Enable two-factor authentication for the user.
     */
    public function enableTwoFactorAuthentication(string $secret): void
    {
        $this->forceFill([
            'two_factor_secret' => encrypt($secret),
            'two_factor_enabled' => true,
            'two_factor_confirmed_at' => now(),
        ])->save();
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disableTwoFactorAuthentication(): void
    {
        $this->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
            'two_factor_enabled' => false,
        ])->save();
    }

    /**
     * Get the decrypted two-factor secret.
     */
    public function getTwoFactorSecret(): ?string
    {
        return $this->two_factor_secret ? decrypt($this->two_factor_secret) : null;
    }

    /**
     * Generate recovery codes.
     */
    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        }
        
        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($codes))
        ])->save();
        
        return $codes;
    }

    /**
     * Get decrypted recovery codes.
     */
    public function getRecoveryCodes(): array
    {
        return $this->two_factor_recovery_codes 
            ? json_decode(decrypt($this->two_factor_recovery_codes), true)
            : [];
    }

    public function advances(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TouringAdvance::class);
    }

    public function claimsApproved(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Claim::class, 'authorising_staff_id');
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

    public function claims(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Claim::class);
    }

    public function documentComments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentComment::class);
    }

    public function documentUpdates(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentUpdate::class);
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

    public function documentDrafts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentDraft::class, 'created_by_user_id');
    }

    public function signed(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Signature::class);
    }

    public function authorisedDocumentDrafts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DocumentDraft::class, 'authorising_staff_id');
    }

    public function flightReservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(FlightReservation::class);
    }

    public function gradeLevel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    public function hotelReservations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HotelReservation::class);
    }

    public function location(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function meetings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Meeting::class, 'user_id');
    }

    public function attendances(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphedByMany(Meeting::class, 'userable');
    }

    public function scheduledMeetings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Meeting::class, 'staff_id');
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

    public function hotelReservationRequests(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(HotelReservation::class, 'staff_id');
    }

    public function supplies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StoreSupply::class);
    }

    public function uploads(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Upload::class);
    }

    public function groups(): \Illuminate\Database\Eloquent\Relations\MorphToMany
    {
        return $this->morphToMany(Group::class, 'groupable');
    }

    public function notificationPreference(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(NotificationPreference::class);
    }
}
