<?php

namespace App\Exceptions\Processor;

class WorkflowContextException extends ProcessorException
{
    public function __construct(string $message = "", int $code = 0, ?\Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous, $context);
    }
}
