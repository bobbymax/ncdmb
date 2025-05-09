<?php

namespace App\Core;

use App\Jobs\DispatchNotificationJob;
use App\Models\{Document, ProgressTracker, User};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NotificationProcessor
{
    protected Document $document;
    protected Collection $notifications;
    protected Collection $recipientEmails;
    protected User $loggedInUser;
    protected int $documentActionId;

    public function __construct(int $documentId, int $authId, int $documentActionId)
    {
        $this->document = processor()->resourceResolver($documentId, 'document');
        $this->loggedInUser = processor()->resourceResolver($authId, 'user');
        $this->notifications = collect();
        $this->recipientEmails = collect();
        $this->documentActionId = $documentActionId;
    }

    public static function for(int $documentId, int $authId, int $documentActionId): static
    {
        return new static($documentId, $authId, $documentActionId);
    }

    public function sendAll(): void
    {
        $this->prepare();

        // Notify initiator and linked users
        $this->notifications
            ->filter(fn ($n) => in_array($n['type'], ['initiator', 'linked_initiator', 'authorising_officer']))
            ->unique('user_id')
            ->each(function ($info) {
                $user = processor()->resourceResolver($info['user_id'], 'user');
                if ($user && $user->email) {
                    DispatchNotificationJob::dispatch(
                        $this->document,
                        $user->email,
                        "$user->surname, $user->firstname",
                        $info['type'],
                        $this->documentActionId,
                    )->onQueue($this->resolveQueue($info['priority'] ?? 'high'));
                }
            });

        // Notify tracker recipients
        $this->notifications
            ->filter(fn ($n) => str_ends_with($n['type'], '_distribution'))
            ->unique('email')
            ->each(function ($info) {
                if (!$info['email']) return;

                DispatchNotificationJob::dispatch(
                    $this->document,
                    $info['email'],
                    $info['name'],
                    $info['type'],
                    $this->documentActionId,
                )->onQueue($this->resolveQueue($info['priority'] ?? 'low'));
            });
    }

    protected function prepare(): void
    {
        // Step 1: Initiator
        $this->notifications->push([
            'user_id' => $this->document->user_id,
            'type' => 'initiator',
            'priority' => 'medium'
        ]);

        // Step 2: Authorising Officer
        $this->notifications->push([
            'user_id' => $this->loggedInUser->id,
            'type' => 'authorising_officer',
            'priority' => 'high'
        ]);

        // Step 3: Linked Document Owners
        foreach ($this->document->linkedDocuments as $linkedDoc) {
            $this->notifications->push([
                'user_id' => $linkedDoc->user_id,
                'type' => 'linked_initiator',
                'priority' => 'low'
            ]);
        }

        // Step 4: Tracker Recipients
        $this->gatherTrackerRecipients();
    }

    protected function resolveQueue(string $priority): string
    {
        return match ($priority) {
            'high' => 'notifications-high',
            'medium' => 'notifications-medium',
            'low' => 'notifications-low',
            default => 'notifications-default'
        };
    }

    protected function gatherTrackerRecipients(): void
    {
        $trackerIds = collect($this->document->linkedDocuments)
            ->pluck('progress_tracker_id')
            ->filter()
            ->unique();

        // Main current tracker
        if ($this->document->progress_tracker_id) {
            $mainTracker = ProgressTracker::with('recipients')->find($this->document->progress_tracker_id);
            if ($mainTracker) {
                $this->addRecipientsFromTracker($mainTracker, 'current_distribution', 'medium');
            }
        }

        // Next tracker
        $nextTracker = $this->getNextTracker($this->document);
        if ($nextTracker) {
            $this->addRecipientsFromTracker($nextTracker, 'next_distribution', 'high');
        }


        // Linked document trackers (exclude current & next tracker if already handled)
        foreach ($trackerIds as $trackerId) {
            if (isset($mainTracker) && $trackerId == $mainTracker->id) continue;
            if (isset($nextTracker) && $trackerId == $nextTracker->id) continue;

            $tracker = ProgressTracker::with('recipients')->find($trackerId);
            if ($tracker) {
                $this->addRecipientsFromTracker($tracker, 'linked_distribution');
            }
        }
    }

    protected function addRecipientsFromTracker(
        ProgressTracker $tracker,
        string $role,
        string $priority = "low"
    ): void {
        foreach ($tracker->recipients as $recipient) {
            $groupId = $recipient->group_id;
            $departmentId = $recipient->department_id != 0
                ? $recipient->department_id
                : ($tracker->document->department_id ?? $this->document->department_id);

            if (!$groupId || !$departmentId) {
                continue;
            }

            User::whereHas('groups', function ($q) use ($groupId) {
                $q->where('groups.id', $groupId);
            })
                ->where('department_id', $departmentId)
                ->select(['id', 'email', 'firstname', 'surname'])
                ->chunk(100, function ($users) use ($role, $priority) {
                    foreach ($users as $user) {
                        $this->notifications->push([
                            'user_id' => $user->id,
                            'email' => $user->email,
                            'name' => "$user->surname, $user->firstname",
                            'type' => $role,
                            'priority' => $priority,
                        ]);
                    }
                });
        }
    }

    protected function getNextTracker(Document $document): ?ProgressTracker
    {
        $workflow = $document->workflow;

        if (!$workflow || !$document->progress_tracker_id) {
            return null;
        }

        $currentTracker = $workflow->trackers->firstWhere('id', $document->progress_tracker_id);

        if (!$currentTracker) {
            return null;
        }

        $nextOrder = $currentTracker->order + 1;
        return $workflow->trackers->firstWhere('order', $nextOrder);
    }
}
