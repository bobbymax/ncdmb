<?php

namespace App\Services;

use App\Repositories\DisseminationChannelRepository;

class DisseminationChannelService extends BaseService
{
    public function __construct(DisseminationChannelRepository $disseminationChannelRepository)
    {
        $this->repository = $disseminationChannelRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }
}
