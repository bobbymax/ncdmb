<?php

namespace App\Repositories;

use App\Models\WorkOrder;

class WorkOrderRepository extends BaseRepository
{
    public function __construct(WorkOrder $workOrder) {
        parent::__construct($workOrder);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
