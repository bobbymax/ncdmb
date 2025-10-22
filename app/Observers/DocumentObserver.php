<?php

namespace App\Observers;

use App\Events\DocumentStageAdvanced;
use App\Models\Document;
use App\Models\ProgressTracker;
use Illuminate\Support\Facades\Log;

class DocumentObserver
{
    /**
     * Handle the Document "updated" event
     * 
     * Detects when a document advances to a new ProgressTracker stage
     * and dispatches the DocumentStageAdvanced event
     */
    public function updated(Document $document): void
    {
        // Check if progress_tracker_id changed (stage advancement)
        if ($document->wasChanged('progress_tracker_id')) {
            $newTrackerId = $document->progress_tracker_id;
            $oldTrackerId = $document->getOriginal('progress_tracker_id');
            
            if ($newTrackerId) {
                $newTracker = ProgressTracker::with(['processCard', 'stage'])->find($newTrackerId);
                $previousTracker = $oldTrackerId ? 
                    ProgressTracker::with(['processCard', 'stage'])->find($oldTrackerId) : null;
                
                if ($newTracker) {
                    Log::info("Document advanced to new stage", [
                        'document_id' => $document->id,
                        'document_ref' => $document->ref,
                        'old_tracker_id' => $oldTrackerId,
                        'old_stage_order' => $previousTracker?->order,
                        'new_tracker_id' => $newTrackerId,
                        'new_stage_order' => $newTracker->order,
                        'new_stage_name' => $newTracker->stage?->name ?? 'Unknown',
                        'has_process_card' => $newTracker->process_card_id ? 'Yes' : 'No',
                    ]);
                    
                    // Dispatch stage advancement event
                    event(new DocumentStageAdvanced($document, $newTracker, $previousTracker));
                }
            }
        }
    }
}

