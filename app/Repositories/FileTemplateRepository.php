<?php

namespace App\Repositories;

use App\Models\FileTemplate;
use Illuminate\Support\Str;

class FileTemplateRepository extends BaseRepository
{
    public function __construct(FileTemplate $fileTemplate) {
        parent::__construct($fileTemplate);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}
