<?php

namespace App\Repositories;

use App\Models\Entity;

class EntityRepository extends BaseRepository
{
    public function __construct(Entity $entity) {
        parent::__construct($entity);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
