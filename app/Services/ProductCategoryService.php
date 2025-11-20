<?php

namespace App\Services;

use App\Repositories\ProductCategoryRepository;
use Illuminate\Support\Str;

class ProductCategoryService extends BaseService
{
    public function __construct(
        ProductCategoryRepository $productCategoryRepository
    ) {
        parent::__construct($productCategoryRepository);
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'sometimes|nullable|string|min:3',
        ];
    }

    public function store(array $data)
    {
        return parent::store([
            ...$data,
            'label' => Str::slug($data['name']),
        ]);
    }
}
