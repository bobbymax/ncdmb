<?php

namespace App\Repositories;

use App\Models\InventoryIssueItem;

class InventoryIssueItemRepository extends BaseRepository
{
    public function __construct(InventoryIssueItem $inventoryIssueItem) {
        parent::__construct($inventoryIssueItem);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
