<?php

namespace App\DTO;

class DocumentActivityContext
{
    public function __construct(
        public int $document_id,
        public int $workflow_stage_id,
        public string $action_performed,
        public array $loggedInUser,
        public array $document_owner,
        public array $department_owner,
        public string $document_ref,
        public string $document_title,
        public string $service,
        public array $pointer,
        public ?array $threads,
        public ?array $watchers,
        public ?string $desk_name = "Unknown Desk"
    ) {}
}
