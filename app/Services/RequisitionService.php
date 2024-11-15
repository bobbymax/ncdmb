<?php

namespace App\Services;

use App\Repositories\RequisitionItemRepository;
use App\Repositories\RequisitionRepository;
use Illuminate\Support\Facades\DB;

class RequisitionService extends BaseService
{
    protected RequisitionItemRepository $requisitionItemRepository;

    public function __construct(
        RequisitionRepository $requisitionRepository,
        RequisitionItemRepository $requisitionItemRepository
    ) {
        parent::__construct($requisitionRepository);
        $this->requisitionItemRepository = $requisitionItemRepository;
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

    public function store(array $data)
    {
        return  DB::transaction(function () use ($data) {
            $requisition = parent::store($data);

            if ($requisition && isset($data['items'])) {
                foreach ($data['items'] as $item) {
                    $this->requisitionItemRepository->create([
                        'requisition_id' => $requisition->id,
                        ...$item,
                    ]);
                }
            }

            return $requisition;
        });
    }
}
