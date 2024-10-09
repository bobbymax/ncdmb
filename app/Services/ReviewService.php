<?php

namespace App\Services;

use App\Repositories\ReviewRepository;

class ReviewService extends BaseService
{
    public function __construct(ReviewRepository $reviewRepository)
    {
        $this->repository = $reviewRepository;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer|exists:users,id',
            'activity_id' => 'required|integer|exists:activities,id',
            'comment' => 'required|string'
        ];
    }
}
