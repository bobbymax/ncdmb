<?php

namespace App\Repositories;

use App\Models\RequisitionItem;

class RequisitionItemRepository extends BaseRepository
{
    public function __construct(RequisitionItem $requisitionItem) {
        parent::__construct($requisitionItem);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
