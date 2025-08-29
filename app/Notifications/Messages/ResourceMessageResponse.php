<?php

namespace App\Notifications\Messages;

use App\Notifications\Messages\ServiceMessage;
use Illuminate\Support\Arr;

class ResourceMessageResponse implements ServiceMessage
{
    public function __construct(
        protected string $resource,
        protected string $document_category,
        protected string $action,
        protected string $resourceType,
        protected int|string $resourceId,
        protected array $context = []
    ) {}

    public function title(): string
    {
        $ref = Arr::get($this->context, 'documentRef', '#'.$this->resourceId);
        return "{$this->resource} $ref {$this->action}";
    }

    public function summary(): string
    {
        $tracker = Arr::get($this->context, 'currentTracker');
        $stage = is_array($tracker) ? ($tracker['stage'] ?? null)
            : (is_object($tracker) && method_exists($tracker, 'stage') ? $tracker->stage?->label : null);

        $ref = Arr::get($this->context, 'documentRef', '#'.$this->resourceId);
        $line = "Your {$this->resourceType} {$ref} was {$this->action}";
        if ($stage) $line .= " at stage: {$stage}";
        if ($url = Arr::get($this->context, 'url')) $line .= ". View: {$url}";
        return $line . ".";
    }

    public function toArray(): array
    {
        return [
            'service'          => 'claim',
            'action'           => $this->action,
            'documentCategory' => $this->document_category,
            'resource'         => ['type' => $this->resourceType, 'id' => $this->resourceId],
            'context'          => $this->context,
        ];
    }

    protected function humanAction(): string
    {
        return match ($this->action) {
            'submitted' => 'submitted',
            'approved'  => 'approved',
            'rejected'  => 'rejected',
            'queried'   => 'queried',
            default     => $this->action,
        };
    }
}
