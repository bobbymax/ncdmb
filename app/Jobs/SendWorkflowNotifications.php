<?php

namespace App\Jobs;

use App\Mail\WorkflowNotification;
use App\Models\Document;
use App\Models\DocumentAction;
use App\Models\ProgressTracker;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendWorkflowNotifications implements ShouldQueue
{
    use Dispatchable, Queueable, InteractsWithQueue, SerializesModels;

    protected Document $document;
    protected DocumentAction $documentAction;
    protected ProgressTracker $tracker;
    protected int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        Document $document,
        DocumentAction $documentAction,
        ProgressTracker $tracker,
        int $userId
    ) {
        $this->document = $document;
        $this->documentAction = $documentAction;
        $this->tracker = $tracker;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $user = User::find($this->userId);

            if (!$user) {
                Log::error("User not found with ID: {$this->userId}");
                return;
            }

            $recipients = $this->getEmails($this->tracker->recipients);

            foreach($recipients as $email) {
                Mail::to($email)->queue(new WorkflowNotification(
                    $this->document,
                    $this->documentAction,
                    $this->tracker,
                    $user
                ));

                Log::info("Workflow notification sent to: {$email}");
            }
        } catch (\Exception $e) {
            Log::error('Error sending workflow notifications' . $e->getMessage());
        }
    }

    private function getEmails($recipients): array
    {
        if (!($recipients instanceof Collection)) {
            return [];
        }

        $emails = [];

        foreach ($recipients as $recipient) {
            $departmentId = $recipient->department_id > 0 ? $recipient->department_id : $this->document->department_id;

            // Ensure group and users exist before calling pluck()
            if ($recipient->group && $recipient->group->users) {
                $userEmails = $recipient->group->users
                    ->where('department_id', $departmentId)
                    ->pluck('email')
                    ->toArray();

                if (!empty($userEmails)) {
                    $emails = array_merge($emails, $userEmails);
                }
            }
        }

        // Add fallback email if no valid emails were found
        return !empty($emails) ? $emails : ['fallback@example.com'];
    }
}
