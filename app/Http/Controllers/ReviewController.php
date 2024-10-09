<?php

namespace App\Http\Controllers;

use App\Services\ReviewService;

class ReviewController extends Controller
{
    public function __construct(ReviewService $reviewService) {
        $this->service = $reviewService;
        $this->name = 'Review';
    }
}
