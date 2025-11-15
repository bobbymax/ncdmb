<?php

namespace App\Services;

use App\Repositories\ProductMeasurementRepository;
use App\Repositories\ProductRepository;
use Illuminate\Support\Facades\DB;

class ProductService extends BaseService
{
    protected ProductMeasurementRepository $productMeasurementRepository;
    public function __construct(
        ProductRepository $productRepository,
        ProductMeasurementRepository $productMeasurementRepository
    ) {
        parent::__construct($productRepository);
        $this->productMeasurementRepository = $productMeasurementRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'product_category_id' => 'required|integer|exists:product_categories,id',
            'department_id' => 'required|integer|exists:departments,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|min:3',
            'restock_qty' => 'sometimes|integer|min:1',
            'owner' => 'required|string|in:store,other',
            'out_of_stock' => 'sometimes|boolean',
            'is_blocked' => 'sometimes|boolean',
            'request_on_delivery' => 'required|boolean',
            'measurements' => 'nullable|array',
        ];
    }

    public function update(int $id, array $data, $parsed = true)
    {
        return DB::transaction(function () use ($id, $data, $parsed) {
            $product = parent::update($id, $data, $parsed);

            if (!$product) {
                return null;
            }

            $measurementsInput = $data['measurements'] ?? null;

            if (is_array($measurementsInput)) {
                $payload = collect($measurementsInput)
                    ->filter(fn ($row) => !empty($row['measurement_type_id']))
                    ->map(fn ($row) => [
                        'measurement_type_id' => (int) $row['measurement_type_id'],
                        'quantity'            => (int) ($row['quantity'] ?? 0),
                    ]);

                $frontendMeasurementTypeIds = $payload->pluck('measurement_type_id')->toArray();
                $existingMeasurements     = $product->measurements;
                $backendMeasurementTypeIds = $existingMeasurements->pluck('measurement_type_id')->toArray();

                $newMeasurementTypeIds     = array_diff($frontendMeasurementTypeIds, $backendMeasurementTypeIds);
                $sharedMeasurementTypeIds  = array_intersect($backendMeasurementTypeIds, $frontendMeasurementTypeIds);

                foreach ($measurementsInput as $obj) {
                    if (in_array($obj['measurement_type_id'], $newMeasurementTypeIds, true)) {
                        $this->productMeasurementRepository->create([
                            'product_id' => $product->id,
                            'measurement_type_id' => $obj['measurement_type_id'],
                            'quantity' => $obj['quantity'],
                        ]);
                    }

                    if (in_array($obj['measurement_type_id'], $sharedMeasurementTypeIds, true)) {
                        $this->productMeasurementRepository->update($obj['id'], [
                            'quantity' => $obj['quantity'],
                        ]);
                    }
                }
            }

            return $product;
        });
    }
}
