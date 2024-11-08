<?php

namespace App\Repositories;

use App\Models\LifServiceCategory;

class LifServiceCategoryRepository extends BaseRepository
{
    public function __construct(LifServiceCategory $lifServiceCategory) {
        parent::__construct($lifServiceCategory);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
