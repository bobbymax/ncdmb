<?php

namespace App\Handlers;

class DataNotFound extends \Exception
{
    public function __construct(string $message = "Record not on our database!")
    {
        parent::__construct($message);
    }
}
