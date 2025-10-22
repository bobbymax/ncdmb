<?php

namespace App\Exceptions;

use Exception;

class NotificationProcessingException extends Exception
{
    public function __construct(string $message = 'Notification processing failed', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
