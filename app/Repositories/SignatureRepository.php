<?php

namespace App\Repositories;

use App\Engine\Puzzle;
use App\Models\Signature;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class SignatureRepository extends BaseRepository
{
    public function __construct(Signature $signature) {
        parent::__construct($signature);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'signature' => Puzzle::scramble($data['signature'], Auth::user()->staff_no),
        ];
    }
}
