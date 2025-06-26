<?php

namespace App\Repositories;

use App\Models\Block;

class BlockRepository extends BaseRepository
{
    public function __construct(Block $block) {
        parent::__construct($block);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
