<?php

namespace App\Services;

use App\Http\Resources\RequisitionResource;
use App\Models\Requisition;
use App\Repositories\RequisitionRepository;

class RequisitionService extends BaseService
{
    public function __construct(
        RequisitionRepository $requisitionRepository,
        RequisitionResource $requisitionResource
    ) {
        parent::__construct($requisitionRepository, $requisitionResource);
    }

    public function rules($action = "store"): array
    {
        return [
            'remarks' => 'nullable|string',
            'type' => 'required|string|in:custom,quota',
            'status' => 'sometimes|string|in:pending,registered,in-progress,approved,rejected',
            'date_approved_or_rejected' => 'sometimes|nullable|date',
            'items' => 'required|array',
        ];
    }
}
