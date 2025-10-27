<?php

namespace App\Services;

use App\Repositories\DepartmentRepository;
use App\Repositories\GroupRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class RecipientResolverService implements RecipientResolverServiceInterface
{
    public function __construct(
        protected UserRepository $userRepository,
        protected DepartmentRepository $departmentRepository,
        protected GroupRepository $groupRepository
    ) {}

    /**
     * Resolve notification recipients for a tracker
     */
    public function resolveTrackerRecipients(array $tracker, array $loggedInUser = []): Collection
    {
        $recipients = collect();

        try {
            // Check if tracker has specific user_id
            if (($tracker['user_id'] ?? 0) > 0) {
                $this->resolveUserRecipients($tracker['user_id'], $recipients);
            } else {
                // Resolve via department and group
                $this->resolveGroupDepartmentRecipients($tracker, $recipients, $loggedInUser);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to resolve tracker recipients', [
                'tracker' => $tracker,
                'error' => $e->getMessage()
            ]);
        }

        return $recipients;
    }

    /**
     * Resolve watcher recipients
     */
    public function resolveWatcherRecipients(array $watchers): Collection
    {
        $recipients = collect();

        try {
            foreach ($watchers as $watcher) {
                $this->resolveWatcherRecipient($watcher, $recipients);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to resolve watcher recipients', [
                'watchers' => $watchers,
                'error' => $e->getMessage()
            ]);
        }

        return $recipients;
    }

    /**
     * Resolve recipients for a specific user
     */
    protected function resolveUserRecipients(int $userId, Collection $recipients): void
    {
        try {
            $user = $this->userRepository->find($userId);
            if ($user) {
                $recipients->push([
                    'id' => $user->id,
                    'firstname' => $user->firstname ?? '',
                    'surname' => $user->surname ?? '',
                    'email' => $user->email ?? '',
                    'phone' => '',
                    'type' => 'user'
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to resolve user for tracker', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Resolve recipients for group and department combination
     */
    protected function resolveGroupDepartmentRecipients(array $tracker, Collection $recipients, array $loggedInUser = []): void
    {
        $departmentId = $tracker['department_id'] ?? 0;
        $groupId = $tracker['group_id'] ?? 0;

        // If department_id is 0, use the logged-in user's department_id
        if ($departmentId === 0 && !empty($loggedInUser['department_id'])) {
            $departmentId = $loggedInUser['department_id'];
            Log::info('Using logged-in user department_id for tracker resolution', [
                'original_department_id' => $tracker['department_id'] ?? 0,
                'logged_in_user_department_id' => $departmentId,
                'group_id' => $groupId
            ]);
        }

        if ($groupId > 0) {
            try {
                $group = $this->groupRepository->find($groupId);

                if ($group) {
                    // Get users that belong to the group
                    $query = $group->users();

                    // If department_id > 0, filter by department, otherwise get all users in the group
                    if ($departmentId > 0) {
                        $query->where('department_id', $departmentId);
                    }

                    $users = $query->get(['id', 'firstname', 'surname', 'email']);

                    Log::info('Resolved group/department recipients', [
                        'group_id' => $groupId,
                        'department_id' => $departmentId,
                        'user_count' => $users->count()
                    ]);

                    foreach ($users as $user) {
                        $recipients->push([
                            'id' => $user->id,
                            'firstname' => $user->firstname ?? '',
                            'surname' => $user->surname ?? '',
                            'email' => $user->email ?? '',
                            'phone' => '',
                            'type' => 'group_department'
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to resolve group/department users for tracker', [
                    'department_id' => $departmentId,
                    'group_id' => $groupId,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Resolve a single watcher recipient
     */
    protected function resolveWatcherRecipient(array $watcher, Collection $recipients): void
    {
        try {
            $type = $watcher['type'] ?? '';
            $id = $watcher['id'] ?? 0;

            if ($type === 'user') {
                // Direct user watcher
                $recipients->push([
                    'id' => $id,
                    'firstname' => $watcher['name'] ?? '',
                    'surname' => '',
                    'email' => $watcher['email'] ?? '',
                    'phone' => $watcher['phone'] ?? '',
                    'type' => 'watcher_user'
                ]);
            } elseif ($type === 'group') {
                // Group watcher - get users in group for the department
                $this->resolveGroupWatcherRecipients($id, $watcher, $recipients);
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to process watcher', [
                'watcher' => $watcher,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Resolve recipients for a group watcher
     */
    protected function resolveGroupWatcherRecipients(int $groupId, array $watcher, Collection $recipients): void
    {
        try {
            $group = $this->groupRepository->find($groupId);
            if ($group) {
                $departmentId = $watcher['department_id'] ?? 0;
                $users = $group->users()
                    ->whereHas('department', function ($query) use ($departmentId) {
                        $query->where('id', $departmentId);
                    })
                    ->get(['id', 'firstname', 'surname', 'email']);

                foreach ($users as $user) {
                    $recipients->push([
                        'id' => $user->id,
                        'firstname' => $user->firstname ?? '',
                        'surname' => $user->surname ?? '',
                        'email' => $user->email ?? '',
                        'phone' => '',
                        'type' => 'watcher_group'
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to resolve group watcher', [
                'group_id' => $groupId,
                'watcher' => $watcher,
                'error' => $e->getMessage()
            ]);
        }
    }
}
