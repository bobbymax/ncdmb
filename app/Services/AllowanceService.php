<?php

namespace App\Services;

use App\Repositories\AllowanceRepository;
use App\Repositories\GradeLevelRepository;
use App\Repositories\RemunerationRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AllowanceService extends BaseService
{
    protected RemunerationRepository $remunerationRepository;
    protected GradeLevelRepository $gradeLevelRepository;

    public function __construct(
        AllowanceRepository $allowanceRepository,
        RemunerationRepository $remunerationRepository,
        GradeLevelRepository $gradeLevelRepository
    ) {
        parent::__construct($allowanceRepository);
        $this->remunerationRepository = $remunerationRepository;
        $this->gradeLevelRepository = $gradeLevelRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'parent_id' => 'sometimes|integer|min:0',
            'days_required' => 'required',
            'departure_city_id' => 'sometimes|integer|min:0',
            'destination_city_id' => 'sometimes|integer|min:0',
            'description' => 'nullable|string|min:3',
            'category' => 'required|string|in:parent,item',
            'component' => 'required|string|max:255',
            'payment_basis' => 'required|string|max:255',
            'payment_route' => 'required|string|in:one-off,round-trip,computable',
            'selectedRemunerations' => 'sometimes|array',
        ];
    }

    public function store(array $data)
    {
        return  DB::transaction(function () use ($data) {
            $allowance = parent::store($data);

            if ($allowance && isset($data['selectedRemunerations']) && is_array($data['selectedRemunerations']) && count($data['selectedRemunerations']) > 0) {
                foreach ($data['selectedRemunerations'] as $value) {
                    foreach ($value['gradeLevels'] as $grade) {
                        $gradeLevel = $this->gradeLevelRepository->find($grade['value']);

                        if ($gradeLevel) {
                            $this->remunerationRepository->create([
                                'grade_level_id' => $gradeLevel->id,
                                'allowance_id' => $allowance->id,
                                'amount' => (float) $value['amount'],
                                'start_date' => Carbon::now(),
                                'expiration_date' => null,
                                'currency' => $value['currency'] ?? 'NGN',
                            ]);
                        }
                    }
                }
            }

            return $allowance;
        });
    }

    public function update(int $id, array $data, $parsed = true)
    {
        return  DB::transaction(function () use ($id, $data) {
            $allowance = parent::update($id, $data);

            if ($allowance && isset($data['selectedRemunerations']) && is_array($data['selectedRemunerations']) && count($data['selectedRemunerations']) > 0) {
                foreach ($data['selectedRemunerations'] as $value) {
                    foreach ($value['gradeLevels'] as $grade) {
                        $gradeLevel = $this->gradeLevelRepository->find($grade['value']);
                        $remuneration = $this->remunerationRepository
                            ->instanceOfModel()
                            ->where('grade_level_id', $gradeLevel->id)
                            ->where('allowance_id', $allowance->id)
                            ->first();

                        if ($gradeLevel && !$remuneration) {
                            $this->remunerationRepository->create([
                                'grade_level_id' => $gradeLevel->id,
                                'allowance_id' => $allowance->id,
                                'amount' => (float) $value['amount'],
                                'start_date' => Carbon::now(),
                                'expiration_date' => null,
                            ]);
                        }
                    }
                }
            }

            return $allowance;
        });
    }

    public function destroy(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            $record = parent::show($id);

            if ($record) {
                $record->remunerations()->delete();
            }

            return parent::destroy($id);
        });
    }
}
