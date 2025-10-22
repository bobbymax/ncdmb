<?php

namespace App\Repositories;

use App\Models\Conversation;

class ConversationRepository extends BaseRepository
{
    public function __construct(Conversation $conversation) {
        parent::__construct($conversation);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
