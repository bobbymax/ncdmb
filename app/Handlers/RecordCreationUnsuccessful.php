<?php

namespace App\Handlers;

class RecordCreationUnsuccessful extends \Exception
{
    public function __construct(string $message = "Record creation failed!!.")
    {
        parent::__construct($message);
    }
}
