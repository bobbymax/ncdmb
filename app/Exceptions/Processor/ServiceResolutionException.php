<?php

namespace App\Exceptions\Processor;

class ServiceResolutionException extends ProcessorException
{
    public function __construct(string $key, string $message = "", int $code = 0, ?\Exception $previous = null)
    {
        $message = $message ?: "Failed to resolve service with key: {$key}";
        parent::__construct($message, $code, $previous, ['key' => $key]);
    }
}
