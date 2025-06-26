<?php

namespace App\Services;

use App\Repositories\BlockRepository;

class BlockService extends BaseService
{
    public function __construct(BlockRepository $blockRepository)
    {
        parent::__construct($blockRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            //
        ];
    }
}
