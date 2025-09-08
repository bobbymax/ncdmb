<?php

namespace App\Listeners;

use App\Events\DocumentActionPerformed;
use App\Models\Group;
use App\Models\User;
use App\Notifications\DocumentActivityNotification;
use App\Notifications\IdempotencyGuard;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

class SendDocumentActivityNotification implements ShouldQueue
{
    public string $queue = 'notifications';    // keep queues separate (emails, sms, etc.)

    /**
     * Handle the event.
     * @throws \Throwable
     */
    public function handle(DocumentActionPerformed $e): void
    {
        $ctx = $e->context;
        $guard = app(IdempotencyGuard::class);

        // 1) Build audience sets
        $creatorId  = $ctx->loggedInUser['id'];
        $ownerId    = $ctx->document_owner['value'];

        $toOwner    = $creatorId !== $ownerId; // owner gets “created on your behalf”
        $toCreator  = $creatorId !== $ownerId; // creator gets acknowledgment

        $audiences = collect();

        if ($toOwner) {
            $audiences->push([
                'type' => 'owner',
                'users' => User::whereKey($ownerId)->get(['id','firstname','surname','email'])
            ]);
            $audiences->push([
                'type' => 'creator_ack',
                'users' => User::whereKey($creatorId)->get(['id','firstname','surname','email'])
            ]);
        } else {
            // same person created for self → maybe just one “created” message
            $audiences->push([
                'type' => 'self_created',
                'users' => User::whereKey($ownerId)->get(['id','firstname','surname','email'])
            ]);
        }

        // Watchers (direct users)
        $watcherUserIds = collect($ctx->watchers)->where('type', '=', 'user')->pluck('id')->filter()->values();
        if ($watcherUserIds->isNotEmpty()) {
            $audiences->push([
                'type' => 'watcher',
                'users' => User::whereIn('id', $watcherUserIds)->get(['id','firstname','surname','email'])
            ]);
        }

        // Watchers (groups => users)
        $groupIds = collect($ctx->watchers)->where('type', '=', 'group')->pluck('id')->filter()->values();
        if ($groupIds->isNotEmpty()) {
            $groupUsers = collect();

            foreach ($groupIds as $gid) {
                $users = Cache::remember("group_users:{$gid}", 600, function () use ($gid) {
                    return Group::with(['users:id,firstname,surname,email'])
                        ->find($gid)?->users->map->only(['id','firstname','surname','email']) ?? collect();
                });

                $groupUsers = $groupUsers->concat($users);
            }

            $groupUsers = $groupUsers->unique('id')->values();

            if ($groupUsers->isNotEmpty()) {
                $audiences->push([
                    'type' => 'watcher_group',
                    'users' => User::whereIn('id', $groupUsers->pluck('id'))->get(['id','firstname','surname','email'])
                ]);
            }
        }

        // 2) Merge & de-dupe users while preserving audience type tags
        $userBuckets = $this->taggedUsers($audiences);

        // 3) Batch send (queue each notification job)
        Bus::batch(
            $userBuckets->map(function ($payload) use ($ctx, $guard) {
                return function () use ($payload, $ctx, $guard) {
                    [$user, $audTags] = $payload;

                    $key = IdempotencyGuard::key(
                        document_id: $ctx->document_id,
                        pointer: $ctx->pointer['identifier'],
                        action_performed: $ctx->action_performed,
                        userId: $user->id,
                    );

                    if ($guard->alreadySent($key)) {
                        return; // skip duplicate
                    }

                    $user->notify(
                        \App\Notifications\DocumentActivityNotification::fromContext($ctx, $audTags)
                    );
                };
            })
        )->name('document-notifications:'.$ctx->document_id)
            ->allowFailures()
            ->onQueue('notifications')
            ->dispatch();
    }

    private function taggedUsers(Collection $audiences): Collection
    {
        // Returns collection of [User, array audienceTags] with de-duped users
        $map = [];
        foreach ($audiences as $aud) {
            foreach ($aud['users'] as $u) {
                $key = $u->id;
                $map[$key] ??= [$u, []];
                $map[$key][1][] = $aud['type'];
            }
        }
        return collect(array_values($map));
    }
}
