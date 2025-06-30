<?php

namespace App\Services;

use App\Repositories\BlockRepository;

class BlockService extends BaseService
{
    public function __construct(BlockRepository $blockRepository)
    {
        parent::__construct($blockRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'title' => 'required|string|max:255',
            'data_type' => 'required|string',
            'input_type' => 'required|string',
            'icon' => 'required|string',
            'max_words' => 'sometimes|min:0',
            'type' => 'required|string|in:staff,third-party,document'
        ];
    }
}
