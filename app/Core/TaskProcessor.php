<?php

namespace App\Core;

use App\DTO\ProcessedIncomingData;

class TaskProcessor
{
    public string $serviceClass;
    public string $method;
    public ProcessedIncomingData $payload;
    public array $args;

    public function __construct(
        string $serviceClass,
        string $method,
        ProcessedIncomingData $payload,
        array $args = []
    ) {
        $this->serviceClass = $serviceClass;
        $this->method = $method;
        $this->payload = $payload;
        $this->args = $args;
    }

    public function execute(): mixed
    {
        $service = app($this->serviceClass);

        if (!method_exists($service, $this->method)) {
            throw new \BadMethodCallException("Method [{$this->method}] does not exist on service [{$this->serviceClass}].");
        }

        return call_user_func_array([$service, $this->method], [$this->payload, ...$this->args]);
    }
}
