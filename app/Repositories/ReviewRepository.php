<?php

namespace App\Repositories;

use App\Models\Review;

class ReviewRepository extends BaseRepository
{
    public function __construct(Review $review) {
        parent::__construct($review);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
