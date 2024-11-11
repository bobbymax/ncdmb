<?php

namespace App\Repositories;

use App\Models\ProductBrand;
use Illuminate\Support\Str;

class ProductBrandRepository extends BaseRepository
{
    public function __construct(ProductBrand $productBrand) {
        parent::__construct($productBrand);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}
