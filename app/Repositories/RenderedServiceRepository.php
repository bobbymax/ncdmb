<?php

namespace App\Repositories;

use App\Models\RenderedService;

class RenderedServiceRepository extends BaseRepository
{
    public function __construct(RenderedService $renderedService) {
        parent::__construct($renderedService);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
