<?php

namespace App\Repositories;

use App\Models\Upload;
use App\Traits\DocumentFlow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class UploadRepository extends BaseRepository
{
    use DocumentFlow;

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
