<?php

namespace App\Imports;

use App\Interfaces\Importable;
use App\Models\ProductCategory;
use App\Models\User;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductImport implements Importable
{
    public function __construct(protected  ProductRepository $productRepository) {}

    public function import(array $rows): array
    {
        if (empty($rows)) {
            Log::error("handleFundUpload: Data array is empty.");
            return [];
        }

        return DB::transaction(function () use ($rows) {
            $actor = Auth::user() ?? User::where('staff_no', 'ADMIN')->first();
            $departmentId = $actor?->department_id ?? 1;

            $inserts = [];
            $inserted = 0;
            $skipped = 0;
            $formatted = [];

            foreach ($rows as $row) {
                if (!is_array($row) || empty($row)) {
                    $skipped++;
                    continue;
                }

                if (empty($row['category'])) {
                    Log::warning('Skipping product row without category: ' . json_encode($row));
                    $skipped++;
                    continue;
                }

                $categoryLabel = Str::slug($row['category'] ?? '');
                $category = ProductCategory::where('label', $categoryLabel)->first();

                if (!$category) {
                    $category = ProductCategory::create([
                        'name' => $row['category'] ?? 'Not Valid Category',
                        'label' => $categoryLabel ?? uniqid(),
                    ]);
                }

                if (in_array(Str::slug($row['name']), $formatted)) {
                    continue;
                }

                $inserts[] = [
                    'product_category_id' => $category->id,
                    'department_id' => $departmentId,
                    'product_brand_id' => 0,
                    'name' => $row['name'] ?? 'Not Valid Brand',
                    'label' => Str::slug($row['name']),
                    'code' => $this->productRepository->generate('code', 'PROD'),
                    'description' => $row['description'] ?? null,
                    'restock_qty' => isset($row['restock_qty']) ? (float) $row['restock_qty'] : 0,
                    'reorder_point' => isset($row['reorder_point']) ? (float) $row['reorder_point'] : 0,
                    'max_stock_level' => isset($row['max_stock_level']) ? (float) $row['max_stock_level'] : 0,
                    'track_batches' => strtolower((string) ($row['track_batches'] ?? 'no')) === 'yes' ? 1 : 0,
                    'owner' => $row['owner'] ?? 'store',
                    'request_on_delivery' => 0,
                    'out_of_stock' => $row['out_of_stock'] ?? 0,
                    'is_blocked' => $row['is_blocked'] ?? 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $formatted[] = Str::slug($row['name']);
            }

            if (!empty($inserts)) {
                try {
                    $this->productRepository->insert($inserts);
                    $inserted = count($inserts);
                    Log::info("handleFundUpload: Insert data successfully.");
                } catch (\Exception $e) {
                    Log::error($e->getMessage());
                }
            } else {
                Log::error("handleFundUpload: Data array is empty.");
            }

            return [
                'inserted' => $inserted,
                'skipped' => $skipped,
                'total' => count($rows),
            ];
        });
    }
}
