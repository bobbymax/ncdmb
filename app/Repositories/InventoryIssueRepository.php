<?php

namespace App\Repositories;

use App\Models\InventoryIssue;

class InventoryIssueRepository extends BaseRepository
{
    public function __construct(InventoryIssue $inventoryIssue) {
        parent::__construct($inventoryIssue);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
