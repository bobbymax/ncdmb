<?php

namespace App\Repositories;

use App\Models\DisseminationChannel;

class DisseminationChannelRepository extends BaseRepository
{
    public function __construct(DisseminationChannel $disseminationChannel) {
        parent::__construct($disseminationChannel);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
