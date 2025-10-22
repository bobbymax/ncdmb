<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    |
    | Centralized configuration for all notification types and channels.
    | This allows easy modification of notification behavior without code changes.
    |
    */

    'workflow' => [
        /*
        |--------------------------------------------------------------------------
        | Workflow Notification Recipients
        |--------------------------------------------------------------------------
        |
        | Configure which notification channels are used for different recipient types.
        | Current tracker gets highest priority, watchers get lowest priority.
        |
        */
        'recipients' => [
            'current_tracker' => [
                'channels' => ['mail', 'database'],
                'priority' => 'high',
                'queue' => 'notifications-high',
            ],
            'previous_tracker' => [
                'channels' => ['mail', 'database'],
                'priority' => 'medium',
                'queue' => 'notifications-medium',
            ],
            'watchers' => [
                'channels' => ['database'], // mail disabled by default
                'priority' => 'low',
                'queue' => 'notifications-low',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Notification Templates
        |--------------------------------------------------------------------------
        |
        | Define message templates for different notification types.
        | Templates use Laravel's string interpolation for dynamic content.
        |
        */
        'templates' => [
            'pending_action' => [
                'subject' => 'Document Awaiting Action - {document_ref}',
                'greeting' => 'Hello {recipient_name},',
                'body' => 'Document {document_ref} ({document_title}) is now awaiting your action at {tracker_name}. Please review and take the necessary action.',
                'action_text' => 'Take Action',
                'action_url' => '{document_url}',
                'footer' => 'Please process this document as soon as possible.',
            ],
            'acknowledgment' => [
                'subject' => 'Action Acknowledged - {document_ref}',
                'greeting' => 'Hello {recipient_name},',
                'body' => 'The action you performed on document {document_ref} ({document_title}) has been acknowledged and the document has moved to the next stage ({tracker_name}).',
                'action_text' => 'View Document',
                'action_url' => '{document_url}',
                'footer' => 'Thank you for your action on this document.',
            ],
            'status_update' => [
                'subject' => 'Document Status Update - {document_ref}',
                'greeting' => 'Hello {recipient_name},',
                'body' => 'Document {document_ref} ({document_title}) has been updated with status \'{action_status}\' at {tracker_name}. Please review the current status.',
                'action_text' => 'View Document',
                'action_url' => '{document_url}',
                'footer' => 'This is a status update notification for the current stage.',
            ],
            'document_action' => [
                'subject' => 'Document Action Notification - {document_ref}',
                'greeting' => 'Hello {recipient_name},',
                'body' => 'Document {document_ref} ({document_title}) has been processed by {logged_in_user_name}. You are receiving this notification as a watcher of this document.',
                'action_text' => 'View Document',
                'action_url' => '{document_url}',
                'footer' => 'This is an informational notification about document activity.',
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Queue Configuration
        |--------------------------------------------------------------------------
        |
        | Configure queue settings for different priority levels.
        | Higher priority notifications are processed first.
        |
        */
        'queues' => [
            'high' => 'notifications-high',
            'medium' => 'notifications-medium',
            'low' => 'notifications-low',
        ],

        /*
        |--------------------------------------------------------------------------
        | Retry Configuration
        |--------------------------------------------------------------------------
        |
        | Configure retry settings for failed notifications.
        |
        */
        'retry' => [
            'tries' => 3,
            'backoff' => [5, 15, 30], // seconds
            'timeout' => 60, // seconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Configure queue settings for notification processing.
    |
    */
    'queue' => [
        'process' => env('NOTIFICATIONS_QUEUE_PROCESS', 'notifications'),
        'default' => env('NOTIFICATIONS_QUEUE_DEFAULT', 'default'),
        'high' => env('NOTIFICATIONS_QUEUE_HIGH', 'notifications-high'),
        'medium' => env('NOTIFICATIONS_QUEUE_MEDIUM', 'notifications-medium'),
        'low' => env('NOTIFICATIONS_QUEUE_LOW', 'notifications-low'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Notification Settings
    |--------------------------------------------------------------------------
    |
    | Global settings that apply to all notification types.
    |
    */
    'global' => [
        'enable_notifications' => env('NOTIFICATIONS_ENABLED', true),
        'enable_email' => env('NOTIFICATIONS_EMAIL_ENABLED', true),
        'enable_sms' => env('NOTIFICATIONS_SMS_ENABLED', false),
        'enable_database' => env('NOTIFICATIONS_DATABASE_ENABLED', true),
        'default_queue' => env('NOTIFICATIONS_DEFAULT_QUEUE', 'default'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for SMS notifications (when enabled).
    |
    */
    'sms' => [
        'provider' => env('SMS_PROVIDER', 'twilio'),
        'from' => env('SMS_FROM', ''),
        'api_key' => env('SMS_API_KEY', ''),
        'api_secret' => env('SMS_API_SECRET', ''),
    ],
];
