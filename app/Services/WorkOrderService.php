<?php

namespace App\Services;

use App\Repositories\WorkOrderRepository;

class WorkOrderService extends BaseService
{
    public function __construct(WorkOrderRepository $workOrderRepository)
    {
        parent::__construct($workOrderRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'vendor_id' => 'required|exists:vendors,id',
            'department_id' => 'required|exists:departments,id',
            'fund_id' => 'required|exists:funds,id',
            'title' => 'required|string|max:255',
            'total_cost' => 'required|numeric|min:1',
            'no_of_items' => 'required|integer|min:1',
            'status' => 'required|string|in:pending,in-progress,review,attested,approved,denied',
            'approved_at' => 'sometimes|nullable|date',
            'approved_by' => 'sometimes|nullable|exists:users,id',
            'prepared_by' => 'sometimes|nullable|exists:users,id',
            'rejected_at' => 'sometimes|nullable|date',
            'date_of_attestation' => 'sometimes|nullable|date',
            'date_of_review' => 'sometimes|nullable|date',
            'is_closed' => 'required|boolean',
        ];
    }
}
