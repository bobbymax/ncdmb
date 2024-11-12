<?php

namespace App\Repositories;

use App\Models\Room;

class RoomRepository extends BaseRepository
{
    public function __construct(Room $room) {
        parent::__construct($room);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
