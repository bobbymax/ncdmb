<?php

namespace App\DTOs;

use Illuminate\Queue\SerializesModels;

class ResourceNotificationContext
{
    use SerializesModels;

    public function __construct(
        public string $repositoryClass,      // e.g., "InboundRepository"
        public string $resourceType,          // e.g., "inbound"
        public int $resourceId,               // The model ID
        public string $action,                // created, updated, deleted, assigned
        public int $actorId,                  // Who performed the action
        public array $recipients,             // Array of user IDs to notify
        public array $resourceData,           // Snapshot of the resource data
        public array $metadata = [],          // Additional context
    ) {}

    public function isValid(): bool
    {
        return !empty($this->repositoryClass)
            && !empty($this->resourceType)
            && $this->resourceId > 0
            && !empty($this->action)
            && $this->actorId > 0
            && !empty($this->recipients);
    }

    public function getMissingFields(): array
    {
        $missing = [];
        if (empty($this->repositoryClass)) $missing[] = 'repositoryClass';
        if (empty($this->resourceType)) $missing[] = 'resourceType';
        if ($this->resourceId <= 0) $missing[] = 'resourceId';
        if (empty($this->action)) $missing[] = 'action';
        if ($this->actorId <= 0) $missing[] = 'actorId';
        if (empty($this->recipients)) $missing[] = 'recipients';
        return $missing;
    }

    public function getResourceUrl(): string
    {
        $frontendUrl = env('APP_FRONTEND_URL', 'http://localhost:3000');
        return "{$frontendUrl}/desk/{$this->resourceType}s/{$this->resourceId}/view";
    }

    public function getTemplateVariables(): array
    {
        return [
            'resource_type' => ucfirst(str_replace('_', ' ', $this->resourceType)),
            'resource_id' => $this->resourceId,
            'action' => ucfirst($this->action),
            'actor_id' => $this->actorId,
            'resource_url' => $this->getResourceUrl(),
            'resource_data' => $this->resourceData,
            'metadata' => $this->metadata,
        ];
    }
}

