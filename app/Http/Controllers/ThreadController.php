<?php

namespace App\Http\Controllers;


use App\Events\MessageSent;
use App\Http\Resources\ThreadResource;
use App\Models\Thread;
use App\Services\ThreadService;
use Illuminate\Http\Request;

class ThreadController extends BaseController
{
    public function __construct(ThreadService $threadService) {
        parent::__construct($threadService, 'Thread', ThreadResource::class);
    }

    public function conversations(int $threadId): \Illuminate\Http\JsonResponse
    {
        return $this->success($this->jsonResource::collection($this->service->conversations($threadId)));
    }

    /**
     * @throws \Exception
     */
    public function saveAndSendMessage(Request $request): \Illuminate\Http\JsonResponse
    {
        $thread = $this->service->store($request->all());
        broadcast(new MessageSent($thread->conversations()->latest()->first()))->toOthers();
        return $this->success(new $this->jsonResource($thread));
    }

    public function send(Request $request, $thread): \Illuminate\Http\JsonResponse
    {
        $conversation = $this->service->sendMessage($request->all());

        if (!$conversation) {
            return $this->error(null, 'Conversation was not saved', 422);
        }

        broadcast(new MessageSent($conversation))->toOthers();
        return $this->success(new $this->jsonResource($conversation->thread));
    }
}
