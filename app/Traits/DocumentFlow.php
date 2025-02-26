<?php

namespace App\Traits;

use App\Models\DocumentAction;
use App\Models\Workflow;

trait DocumentFlow
{
    protected function getFirstTrackerId(int $workflowId): int
    {
        $workflow = $this->getWorkflow($workflowId);

        if (!$workflow) {
            return 0;
        }

        $tracker = $workflow->trackers()->where('order', 1)->first();

        return $tracker?->id ?? 0;
    }

    protected function getWorkflow(int $workflowId)
    {
        return Workflow::find($workflowId);
    }

    protected function getCreationDocumentAction(): DocumentAction
    {
        return DocumentAction::where('label', 'create-resource')->first();
    }
}
