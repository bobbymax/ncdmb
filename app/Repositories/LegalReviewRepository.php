<?php

namespace App\Repositories;

use App\Models\LegalReview;

class LegalReviewRepository extends BaseRepository
{
    public function __construct(LegalReview $legalReview) {
        parent::__construct($legalReview);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'review_type' => $data['review_type'] ?? 'contract_review',
            'review_status' => $data['review_status'] ?? 'pending',
            'review_date' => $data['review_date'] ?? now()->toDateString(),
            'reviewed_by' => $data['reviewed_by'] ?? \Illuminate\Support\Facades\Auth::id(),
            'requires_revision' => $data['requires_revision'] ?? false,
        ];
    }
}
