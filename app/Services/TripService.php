<?php

namespace App\Services;
use App\Repositories\ExpenseRepository;
use App\Repositories\TripRepository;
use Illuminate\Support\Facades\DB;

class TripService extends BaseService
{
    protected ExpenseRepository $expenseRepository;
    public function __construct(TripRepository $tripRepository, ExpenseRepository $expenseRepository)
    {
        parent::__construct($tripRepository);
        $this->expenseRepository = $expenseRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'claim_id' => 'required|integer|exists:claims,id',
            'airport_id' => 'sometimes|integer|min:0',
            'departure_city_id' => 'sometimes|integer|min:0',
            'destination_city_id' => 'sometimes|integer|min:0',
            'per_diem_category_id' => 'sometimes|integer|min:0',
            'purpose' => 'required|string|max:255',
            'departure_date' => 'required|date',
            'return_date' => 'required|date',
            'accommodation_type' => 'required|string|in:residence,non-residence',
            'type' => 'required|string|in:flight,road',
            'total_amount_spent' => 'required|numeric|min:0',
            'distance' => 'sometimes|numeric|min:0',
            'route' => 'required|string|in:one-way,return',
            'expenses' => 'required|array',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $trip = parent::store($data);

            if ($trip) {
                foreach ($data['expenses'] as $expense) {
                    $expense['trip_id'] = $trip->id;
                    $expense['claim_id'] = $trip->claim_id;
                    $this->expenseRepository->create($expense);
                }
            }

            return $trip;
        });
    }
}
