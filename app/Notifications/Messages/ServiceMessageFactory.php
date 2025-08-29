<?php

namespace App\Notifications\Messages;

use InvalidArgumentException;

class ServiceMessageFactory
{
    public static function make(
        string $service,
        string $action,
        string $documentCategory,
        string $resourceType,
        int|string $resourceId,
        array $context = []
    ): ServiceMessage {
        try {
            return new ResourceMessageResponse(
                $service,
                $documentCategory,
                $action,
                $resourceType,
                $resourceId,
                $context
            );
        } catch (\Exception $exception) {
            throw new InvalidArgumentException("Unknown Service: $service");
        }
    }
}
