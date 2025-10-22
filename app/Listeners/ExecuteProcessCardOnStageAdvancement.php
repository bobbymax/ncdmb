<?php

namespace App\Listeners;

use App\Events\DocumentStageAdvanced;
use App\Services\ProcessCardExecutionService;
use Illuminate\Support\Facades\Log;

class ExecuteProcessCardOnStageAdvancement
{
    protected ProcessCardExecutionService $service;
    
    public function __construct(ProcessCardExecutionService $service)
    {
        $this->service = $service;
    }
    
    /**
     * Handle the event - Execute ProcessCard when document advances to new stage
     */
    public function handle(DocumentStageAdvanced $event): void
    {
        $document = $event->document;
        $newTracker = $event->newTracker;
        $previousTracker = $event->previousTracker;
        
        // Check if this tracker has a ProcessCard
        if (!$newTracker->process_card_id || !$newTracker->processCard) {
            Log::info("No ProcessCard attached to ProgressTracker", [
                'document_id' => $document->id,
                'tracker_id' => $newTracker->id,
                'stage_order' => $newTracker->order
            ]);
            return;
        }
        
        $processCard = $newTracker->processCard;
        
        // Check if ProcessCard is disabled
        if ($processCard->is_disabled) {
            Log::warning("ProcessCard is disabled", [
                'document_id' => $document->id,
                'process_card_id' => $processCard->id,
                'stage_order' => $newTracker->order
            ]);
            return;
        }
        
        // Get the payment (if document is payment-related)
        $payment = $document->documentable;
        
        if (!$payment || get_class($payment) !== 'App\Models\Payment') {
            Log::info("Document is not payment-related, skipping ProcessCard execution", [
                'document_id' => $document->id,
                'documentable_type' => $document->documentable_type
            ]);
            return;
        }
        
        // Check stage-based execution rules
        $rules = $processCard->rules;
        $stageOrder = $newTracker->order;
        
        // Check if this stage should execute
        if (!$this->shouldExecuteAtStage($processCard, $newTracker)) {
            Log::info("ProcessCard should not execute at this stage", [
                'document_id' => $document->id,
                'process_card_id' => $processCard->id,
                'stage_order' => $stageOrder,
                'min_stage' => $rules['min_stage_order'] ?? null,
                'max_stage' => $rules['max_stage_order'] ?? null,
                'specific_stages' => $rules['execute_at_stages'] ?? null,
            ]);
            return;
        }
        
        // Check custom inputs if required
        if ($this->requiresCustomInputs($processCard, $payment)) {
            Log::info("Waiting for custom inputs before execution", [
                'document_id' => $document->id,
                'process_card_id' => $processCard->id,
                'required_fields' => $rules['custom_input_fields'] ?? []
            ]);
            return;
        }
        
        try {
            // Execute ProcessCard with stage context
            $steps = $this->service->executeAccountingCycleForStage(
                $payment,
                $processCard,
                $newTracker,
                $previousTracker
            );
            
            Log::info("ProcessCard executed on stage advancement", [
                'document_id' => $document->id,
                'payment_id' => $payment->id,
                'process_card_id' => $processCard->id,
                'process_card_name' => $processCard->name,
                'stage_order' => $stageOrder,
                'stage_name' => $newTracker->stage?->name ?? 'Unknown',
                'steps_completed' => count($steps)
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to execute ProcessCard on stage advancement", [
                'document_id' => $document->id,
                'payment_id' => $payment->id,
                'process_card_id' => $processCard->id,
                'stage_order' => $stageOrder,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Notify if configured
            if ($rules['notify_on_failure'] ?? false) {
                // TODO: Send notification to administrators
            }
            
            // Escalate if configured
            if ($rules['escalate_on_repeated_failure'] ?? false) {
                // TODO: Create escalation workflow
            }
        }
    }
    
    /**
     * Determine if ProcessCard should execute at this stage
     */
    protected function shouldExecuteAtStage(
        $processCard,
        \App\Models\ProgressTracker $newTracker
    ): bool {
        $rules = $processCard->rules;
        $stageOrder = $newTracker->order;
        
        // Check minimum stage order
        if (isset($rules['min_stage_order'])) {
            if ($stageOrder < $rules['min_stage_order']) {
                return false;
            }
        }
        
        // Check maximum stage order
        if (isset($rules['max_stage_order'])) {
            if ($stageOrder > $rules['max_stage_order']) {
                return false;
            }
        }
        
        // Check stage-specific execution (if array is not empty)
        if (isset($rules['execute_at_stages']) && is_array($rules['execute_at_stages']) && !empty($rules['execute_at_stages'])) {
            if (!in_array($stageOrder, $rules['execute_at_stages'])) {
                return false;
            }
        }
        
        // Check if should only execute at final stage
        if ($rules['execute_at_final_stage_only'] ?? false) {
            // Get max order for this workflow
            $maxOrder = \App\Models\ProgressTracker::where('workflow_id', $newTracker->workflow_id)
                ->where('document_type_id', $newTracker->document_type_id)
                ->max('order');
            
            if ($stageOrder < $maxOrder) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check if custom inputs are required and missing
     */
    protected function requiresCustomInputs($processCard, $payment): bool
    {
        $rules = $processCard->rules;
        
        if (!($rules['requires_custom_inputs'] ?? false)) {
            return false; // Custom inputs not required
        }
        
        // Check if required fields are present in payment metadata
        $requiredFields = $rules['custom_input_fields'] ?? [];
        
        if (empty($requiredFields)) {
            return false; // No specific fields required
        }
        
        $processMetadata = $payment->process_metadata ?? [];
        
        // Check each required field
        foreach ($requiredFields as $field) {
            if (!isset($processMetadata[$field]) || empty($processMetadata[$field])) {
                return true; // Missing required field
            }
        }
        
        return false; // All required fields present
    }
}

