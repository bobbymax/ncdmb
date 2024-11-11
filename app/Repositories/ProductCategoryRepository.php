<?php

namespace App\Repositories;

use App\Models\ProductCategory;
use Illuminate\Support\Str;

class ProductCategoryRepository extends BaseRepository
{
    public function __construct(ProductCategory $productCategory) {
        parent::__construct($productCategory);
    }

    public function parse(array $data): array
    {
        return [
            ...$data,
            'label' => Str::slug($data['name']),
        ];
    }
}
