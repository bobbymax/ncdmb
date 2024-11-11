<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Support\Str;

class ProductRepository extends BaseRepository
{
    public function __construct(Product $product) {
        parent::__construct($product);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
            'code' => $data['code'] ?? $this->generate('code', 'PROD'),
        ];
    }
}
