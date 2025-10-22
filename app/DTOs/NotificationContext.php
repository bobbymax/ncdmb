<?php

namespace App\DTOs;

use App\Models\Document;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotificationContext
{
    public function __construct(
        public int $documentId,
        public array $currentTracker,
        public ?array $previousTracker,
        public array $trackers,
        public array $loggedInUser,
        public string $actionStatus,
        public array $watchers = [],
        public array $meta_data = [],
        // Additional fields for better context
        public ?int $documentActionId = null,
        public ?string $documentRef = null,
        public ?string $documentTitle = null,
        public ?int $departmentId = null,
        public ?int $userId = null,
    ) {}

    public static function from(Document $document, array $data): self
    {
        return new self(
            documentId: $document->id,
            currentTracker: $data['currentTracker'],
            previousTracker: $data['previousTracker'],
            trackers: $data['trackers'],
            loggedInUser: $data['loggedInUser'],
            actionStatus: $data['actionStatus'],
            watchers: $data['watchers'],
            meta_data: $data['meta_data'],
            // Additional fields from document and data
            documentActionId: $data['documentActionId'] ?? $document->document_action_id ?? null,
            documentRef: $document->ref ?? null,
            documentTitle: $document->title ?? null,
            departmentId: $document->department_id ?? null,
            userId: $data['loggedInUser']['id'] ?? null,
        );
    }

    /**
     * Get the document URL for notifications
     */
    public function getDocumentUrl(): string
    {
        return config('app.url') . "/documents/{$this->documentId}";
    }

    /**
     * Get the logged-in user's full name
     */
    public function getLoggedInUserName(): string
    {
        $firstname = $this->loggedInUser['firstname'] ?? '';
        $surname = $this->loggedInUser['surname'] ?? '';

        return trim("{$firstname} {$surname}") ?: 'User';
    }

    /**
     * Get the current tracker name
     */
    public function getCurrentTrackerName(): string
    {
        return $this->currentTracker['label'] ?? 'Current Stage';
    }

    /**
     * Get the previous tracker name
     */
    public function getPreviousTrackerName(): string
    {
        return $this->previousTracker['label'] ?? 'Previous Stage';
    }

    /**
     * Get the action status in a readable format
     */
    public function getActionStatusText(): string
    {
        return ucfirst(str_replace('_', ' ', $this->actionStatus));
    }

    /**
     * Validate the notification context
     */
    public function isValid(): bool
    {
        // Check for null values explicitly
        if ($this->documentId === null || $this->documentId <= 0) {
            Log::warning('NotificationContext: Invalid documentId', ['document_id' => $this->documentId]);
            return false;
        }
        
        if ($this->currentTracker === null || !is_array($this->currentTracker) || empty($this->currentTracker)) {
            Log::warning('NotificationContext: Invalid currentTracker', ['current_tracker' => $this->currentTracker]);
            return false;
        }
        
        if ($this->trackers === null || !is_array($this->trackers) || empty($this->trackers)) {
            Log::warning('NotificationContext: Invalid trackers', ['tracker_count' => is_array($this->trackers) ? count($this->trackers) : 'not_array']);
            return false;
        }
        
        if ($this->loggedInUser === null || !is_array($this->loggedInUser) || empty($this->loggedInUser)) {
            Log::warning('NotificationContext: Invalid loggedInUser', ['logged_in_user' => $this->loggedInUser]);
            return false;
        }
        
        if ($this->actionStatus === null || empty(trim($this->actionStatus))) {
            Log::warning('NotificationContext: Invalid actionStatus', ['action_status' => $this->actionStatus]);
            return false;
        }
        
        // Validate currentTracker has required fields
        if (empty($this->currentTracker['identifier'])) {
            Log::warning('NotificationContext: Missing currentTracker identifier', [
                'current_tracker' => $this->currentTracker
            ]);
            return false;
        }
        
        // Validate loggedInUser has required fields
        if (empty($this->loggedInUser['id']) || $this->loggedInUser['id'] <= 0) {
            Log::warning('NotificationContext: Invalid loggedInUser id', [
                'logged_in_user_id' => $this->loggedInUser['id'] ?? 'missing'
            ]);
            return false;
        }
        
        Log::info('NotificationContext: Validation passed', [
            'document_id' => $this->documentId,
            'action_status' => $this->actionStatus,
            'current_tracker_identifier' => $this->currentTracker['identifier'],
            'logged_in_user_id' => $this->loggedInUser['id']
        ]);
        
        return true;
    }

    /**
     * Get missing required fields
     */
    public function getMissingFields(): array
    {
        $missing = [];
        
        if ($this->documentId === null || $this->documentId <= 0) {
            $missing[] = 'documentId (null or invalid)';
        }
        
        if ($this->currentTracker === null || !is_array($this->currentTracker) || empty($this->currentTracker)) {
            $missing[] = 'currentTracker (null, not array, or empty)';
        } elseif (!empty($this->currentTracker) && empty($this->currentTracker['identifier'])) {
            $missing[] = 'currentTracker.identifier';
        }
        
        if ($this->trackers === null || !is_array($this->trackers) || empty($this->trackers)) {
            $missing[] = 'trackers (null, not array, or empty)';
        }
        
        if ($this->loggedInUser === null || !is_array($this->loggedInUser) || empty($this->loggedInUser)) {
            $missing[] = 'loggedInUser (null, not array, or empty)';
        } elseif (!empty($this->loggedInUser) && (empty($this->loggedInUser['id']) || $this->loggedInUser['id'] <= 0)) {
            $missing[] = 'loggedInUser.id (missing or invalid)';
        }
        
        if ($this->actionStatus === null || empty(trim($this->actionStatus))) {
            $missing[] = 'actionStatus (null or empty)';
        }
        
        return $missing;
    }

    /**
     * Get template variables for string interpolation
     */
    public function getTemplateVariables(): array
    {
        try {
            $doc = Document::findOrFail($this->documentId);
        } catch (\Exception $e) {
            Log::error('NotificationContext: Document not found', [
                'document_id' => $this->documentId,
                'error' => $e->getMessage()
            ]);
            
            // Return safe fallback values
            return [
                'document_id' => $this->documentId,
                'document_ref' => 'N/A',
                'document_title' => 'Document Not Found',
                'tracker_name' => $this->getCurrentTrackerName(),
                'previous_tracker_name' => $this->getPreviousTrackerName(),
                'action_status' => $this->getActionStatusText(),
                'logged_in_user_name' => $this->getLoggedInUserName(),
                'document_url' => $this->getDocumentUrl(),
            ];
        }

        return [
            'document_id' => $doc?->id,
            'document_ref' => $doc?->ref,
            'document_title' => $doc?->title,
            'tracker_name' => $this->getCurrentTrackerName(),
            'previous_tracker_name' => $this->getPreviousTrackerName(),
            'action_status' => $this->getActionStatusText(),
            'logged_in_user_name' => $this->getLoggedInUserName(),
            'document_url' => $this->getDocumentUrl(),
        ];
    }
}
