<?php

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Thread;
use App\Repositories\ConversationRepository;
use App\Repositories\ThreadRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ThreadService extends BaseService
{
    protected ConversationRepository $conversationRepository;
    public function __construct(ThreadRepository $threadRepository, ConversationRepository $conversationRepository)
    {
        parent::__construct($threadRepository);
        $this->conversationRepository = $conversationRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'document_id' => 'required|integer|exists:documents,id',
            'thread_owner_id' => 'required|integer|exists:users,id',
            'recipient_id' => 'required|integer|exists:users,id',
            'identifier' => 'required|string|unique:threads,identifier',
            'pointer_identifier' => 'required|string|unique:threads,pointer_identifier',
            'icon' => 'sometimes|string',
            'category' => 'required|string',
            'action' => 'required|string',
            'resource' => 'nullable',
            'priority' => 'required|string|in:low,medium,high',
            'status' => 'required|string|in:pending,resolved,rejected',
            'state' => 'required|string|in:open,closed',
            'conversations' => 'required|array',
        ];
    }

    public function conversations(int $id)
    {
        $thread = $this->repository->find($id);
        return $thread->conversations()->latest()->paginate(20);
    }

    public function startConversation(array $data)
    {
        return DB::transaction(function () use ($data) {
            $recipients = $data['recipients'];

            if (!empty($recipients)) {
                foreach ($recipients as $recipient) {
                    $this->repository->create([
                        'document_id' => $data['document_id'],
                        'thread_owner_id' => Auth::id(),
                        'recipient_id' => $recipient,
                        'identifier' => $this->generatePaymentCode("THR"),
                        'pointer_identifier' => $data['pointer_identifier'],
                        'priority' => $data['priority'],
                        'action' => $data['action'],
                    ]);
                }
            }

            return true;
        });
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $thread = $this->repository->create([
                'document_id' => $data['document_id'],
                'thread_owner_id' => $data['thread_owner_id'],
                'recipient_id' => $data['recipient_id'],
                'identifier' => $data['identifier'],
                'pointer_identifier' => $data['pointer_identifier'],
                'category' => $data['category'],
            ]);

            if (!$thread) {
                return null;
            }

            $thread->conversations()->create([
                'thread_id' => $thread->id,
                'sender_id' => Auth::id(),
                'message' => $data['message'],
                'category' => $data['category'],
                'attachments' => $data['attachments'] ?? null
            ]);

            return $thread;
        });
    }

    public function sendMessage(array $data)
    {
        $thread = $this->repository->find($data['thread_id']);

        if (!$thread) {
            return null;
        }

        return $thread->conversations()->create([
            'sender_id' => Auth::id(),
            'message' => $data['message'],
            'attachments' => $data['attachments'] ?? null,
            'category' => $data['category'],
            'is_delivered' => true
        ]);
    }
}
