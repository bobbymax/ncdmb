<?php

namespace App\Repositories;

use App\Models\Broker;

class BrokerRepository extends BaseRepository
{
    public function __construct(Broker $broker) {
        parent::__construct($broker);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
