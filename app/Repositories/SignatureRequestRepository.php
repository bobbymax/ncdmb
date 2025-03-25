<?php

namespace App\Repositories;

use App\Models\SignatureRequest;

class SignatureRequestRepository extends BaseRepository
{
    public function __construct(SignatureRequest $signatureRequest) {
        parent::__construct($signatureRequest);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
