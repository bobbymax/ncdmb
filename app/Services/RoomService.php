<?php

namespace App\Services;

use App\Repositories\RoomRepository;

class RoomService extends BaseService
{
    public function __construct(
        RoomRepository $roomRepository
    ) {
        parent::__construct($roomRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'building_id' => 'required|integer|exists:buildings,id',
            'name' => 'required|string|max:255',
            'room_no' => 'sometimes|nullable|string|max:255',
            'floor' => 'required|integer|min:0',
            'max_capacity' => 'required|integer|min:1',
            'type' => 'required|string|in:hall,room,main',
            'area' => 'required|string|in:wing-a,wing-b,conference-centre,other',
            'status' => 'sometimes|string|in:available,occupied,in-maintenance,decommissioned',
        ];
    }
}
