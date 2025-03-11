<?php

namespace App\Repositories;

use App\Models\ApiRequestLog;

class ApiRequestLogRepository extends BaseRepository
{
    public function __construct(ApiRequestLog $apiRequestLog) {
        parent::__construct($apiRequestLog);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
