<?php

namespace App\Http\Controllers;


use App\Http\Resources\ConversationResource;
use App\Services\ConversationService;

class ConversationController extends BaseController
{
    public function __construct(ConversationService $conversationService) {
        parent::__construct($conversationService, 'Conversation', ConversationResource::class);
    }
}
