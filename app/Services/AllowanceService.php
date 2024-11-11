<?php

namespace App\Services;

use App\Repositories\AllowanceRepository;
use App\Repositories\GradeLevelRepository;
use App\Repositories\RemunerationRepository;
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
        $this->repository = $allowanceRepository;
        $this->remunerationRepository = $remunerationRepository;
        $this->gradeLevelRepository = $gradeLevelRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'parent_id' => 'sometimes|integer|min:0',
            'days_required' => 'required',
            'description' => 'nullable|string|min:3',
            'category' => 'required|string|in:parent,item',
            'remunerations' => 'required|array',
        ];
    }

    public function store(array $data)
    {
        return  DB::transaction(function () use ($data) {
            $allowance = parent::store($data);

            if ($allowance) {
                foreach ($data['remunerations'] as $value) {
                    foreach ($value['grade_levels'] as $str) {
                        $gradeLevel = $this->gradeLevelRepository->find($str);

                        if ($gradeLevel) {
                            $this->remunerationRepository->create([
                                'grade_level_id' => $gradeLevel->id,
                                'allowance_id' => $allowance->id,
                                'amount' => (float) $value['amount'],
                                'start_date' => $value['start_date'],
                            ]);
                        }
                    }
                }
            }

            return $allowance;
        });
    }
}
