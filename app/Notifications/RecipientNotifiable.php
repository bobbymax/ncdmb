<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class RecipientNotifiable extends User
{
    public array $recipient;

    protected $table = "users";

    public function __construct(array $recipient = [])
    {
        parent::__construct();
        $this->recipient = $recipient;
    }


    /**
     * Route notifications for the mail channel
     */
    public function routeNotificationForMail($notification)
    {
        Log::info('RecipientNotifiable: routeNotificationForMail called', [
            'email' => $this->recipient['email'] ?? 'not_set'
        ]);

        return $this->recipient['email'] ?? null;
    }

    /**
     * Route notifications for the database channel
     */
    public function routeNotificationForDatabase($notification)
    {
        Log::info('RecipientNotifiable: routeNotificationForDatabase called', [
            'recipient_id' => $this->recipient['id'] ?? 'unknown'
        ]);

        // Don't return anything - let Laravel handle the database channel automatically
        // The database channel will use the notifiable's ID and type from the model
        return null;
    }

    /**
     * Route notifications for the SMS channel
     */
    public function routeNotificationForSms($notification)
    {
        Log::info('RecipientNotifiable: routeNotificationForSms called', [
            'phone' => $this->recipient['phone'] ?? 'not_set'
        ]);

        return $this->recipient['phone'] ?? null;
    }

    /**
     * Get the recipient's full name
     */
    public function getFullName(): string
    {
        $firstname = $this->recipient['firstname'] ?? '';
        $surname = $this->recipient['surname'] ?? '';

        return trim("{$firstname} {$surname}") ?: 'User';
    }
}
