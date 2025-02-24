<?php

namespace App\Engine;

use App\Jobs\SendWorkflowNotifications;
use App\Models\Document;
use App\Models\DocumentAction;
use App\Models\ProgressTracker;
use Illuminate\Support\Facades\Log;

class MailingEngine
{
    public function notifyStageTransition(
        Document $document,
        DocumentAction $documentAction,
        int $userId,
        ProgressTracker $previousTracker,
        ?ProgressTracker $nextTracker = null,
    ): void {
        try {
            // Notify previous stage recipients
            if (!empty($previousTracker->recipients)) {
                dispatch(new SendWorkflowNotifications(
                    $document,
                    $documentAction,
                    $previousTracker,
                    $userId
                ));
            }

            // Notify next stage recipients
            if ($nextTracker && !empty($nextTracker->recipients)) {
                dispatch(new SendWorkflowNotifications(
                    $document,
                    $documentAction,
                    $nextTracker,
                    $userId
                ));
            }

            Log::info("Notifications queued for Document ID: {$document->id}");
        } catch (\Exception $e) {
            Log::error("Notification error for Document ID: {$document->id}. Error: {$e->getMessage()}");
        }
    }
}
