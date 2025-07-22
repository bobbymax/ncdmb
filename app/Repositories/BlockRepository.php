<?php

namespace App\Repositories;

use App\Models\Block;
use Illuminate\Support\Str;

class BlockRepository extends BaseRepository
{
    public function __construct(Block $block) {
        parent::__construct($block);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['title']),
            'contents' => isset($data['contents']) ? json_encode(array_filter($data['contents'], fn($value) => !is_null($value) && $value !== '')) : null,
        ];
    }
}
