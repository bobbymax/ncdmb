<?php

namespace App\Repositories;

use App\Models\Thread;

class ThreadRepository extends BaseRepository
{
    public function __construct(Thread $thread) {
        parent::__construct($thread);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
