<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationRecipient extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'notifiable_id' => 'integer',
    ];

    /**
     * Get the notifiable entity that owns the notification.
     */
    public function notifiable()
    {
        return $this->morphTo();
    }
}
