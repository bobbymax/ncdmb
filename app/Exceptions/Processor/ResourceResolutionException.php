<?php

namespace App\Exceptions\Processor;

class ResourceResolutionException extends ProcessorException
{
    public function __construct(string $serviceKey, string|int|array $value, string $message = "", int $code = 0, ?\Exception $previous = null)
    {
        $message = $message ?: "Failed to resolve resource for service '{$serviceKey}' with value: " . (is_array($value) ? 'array[' . count($value) . ']' : $value);
        parent::__construct($message, $code, $previous, [
            'service_key' => $serviceKey,
            'value' => $value,
            'value_type' => gettype($value)
        ]);
    }
}
