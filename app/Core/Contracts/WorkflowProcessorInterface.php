<?php

namespace App\Core\Contracts;

use App\Models\Document;
use App\Models\DocumentAction;
use App\Models\DocumentDraft;
use App\Models\ProgressTracker;

interface WorkflowProcessorInterface
{
    /**
     * Execute a workflow action on a document
     */
    public function executeAction(string $action, Document $document, ?DocumentAction $documentAction = null): mixed;

    /**
     * Get the current tracker for a document
     */
    public function getCurrentTracker(Document $document): ProgressTracker;

    /**
     * Get the current draft for a document
     */
    public function getCurrentDraft(Document $document): ?DocumentDraft;

    /**
     * Check if a workflow action is valid
     */
    public function isValidAction(string $action): bool;

    /**
     * Get available workflow actions
     */
    public function getAvailableActions(): array;
}
