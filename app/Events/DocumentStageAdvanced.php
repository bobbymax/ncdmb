<?php

namespace App\Events;

use App\Models\Document;
use App\Models\ProgressTracker;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DocumentStageAdvanced
{
    use Dispatchable, SerializesModels;
    
    public Document $document;
    public ProgressTracker $newTracker;
    public ?ProgressTracker $previousTracker;
    
    public function __construct(
        Document $document,
        ProgressTracker $newTracker,
        ?ProgressTracker $previousTracker = null
    ) {
        $this->document = $document;
        $this->newTracker = $newTracker;
        $this->previousTracker = $previousTracker;
    }
}

