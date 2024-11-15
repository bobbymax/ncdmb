<?php

namespace App\Services;

use App\Repositories\GradeLevelRepository;
use App\Repositories\HotelRepository;
use Illuminate\Support\Facades\DB;

class HotelService extends BaseService
{
    protected GradeLevelRepository $gradeLevelRepository;
    public function __construct(
        HotelRepository $hotelRepository,
        GradeLevelRepository $gradeLevelRepository
    ) {
        parent::__construct($hotelRepository);
        $this->gradeLevelRepository = $gradeLevelRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'house_no' => 'nullable|string|max:255',
            'street_one' => 'required|string|max:255',
            'street_two' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'representative' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'not_active' => 'sometimes|boolean',
            'unit_price_per_night' => 'required|numeric|min:0',
            'grade_levels' => 'required|array',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $hotel = parent::store($data);

            if ($hotel) {
                foreach ($data['grade_levels'] as $obj) {
                    $gradeLevel = $this->gradeLevelRepository->find($obj['value']);

                    if ($gradeLevel && !in_array($gradeLevel->id, $hotel->gradeLevels->pluck('id')->toArray())) {
                        $hotel->gradeLevels()->save($gradeLevel);
                    }
                }
            }

            return $hotel;
        });
    }
}
