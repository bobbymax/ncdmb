<?php

namespace App\Services;

use App\Repositories\ApiRequestLogRepository;

class ApiRequestLogService extends BaseService
{
    public function __construct(ApiRequestLogRepository $apiRequestLogRepository)
    {
        parent::__construct($apiRequestLogRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
