<?php

namespace App\Http\Controllers;


use App\Http\Resources\LegalReviewResource;
use App\Services\LegalReviewService;

class LegalReviewController extends BaseController
{
    public function __construct(LegalReviewService $legalReviewService) {
        parent::__construct($legalReviewService, 'LegalReview', LegalReviewResource::class);
    }
}
