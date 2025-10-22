<?php

namespace App\Services;

use App\Repositories\ConversationRepository;

class ConversationService extends BaseService
{
    public function __construct(ConversationRepository $conversationRepository)
    {
        parent::__construct($conversationRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
