<?php

namespace App\Handlers;

class CollectionNotFound extends \Exception
{
    public function __construct(string $message = "Collection not found")
    {
        parent::__construct($message);
    }
}
