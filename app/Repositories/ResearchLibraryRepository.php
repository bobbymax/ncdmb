<?php

namespace App\Repositories;

use App\Models\ResearchLibrary;

class ResearchLibraryRepository extends BaseRepository
{
    public function __construct(ResearchLibrary $researchLibrary) {
        parent::__construct($researchLibrary);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
