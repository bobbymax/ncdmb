<?php

namespace App\Repositories;

use App\Models\Upload;
use Illuminate\Support\Facades\Auth;

class UploadRepository extends BaseRepository
{
    public function __construct(Upload $upload) {
        parent::__construct($upload);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'user_id' => Auth::user()->id,
            'department_id' => Auth::user()->department_id
        ];
    }
}
