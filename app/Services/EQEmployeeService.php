<?php

namespace App\Services;

use App\Repositories\EQEmployeeRepository;

class EQEmployeeService extends BaseService
{
    public function __construct(EQEmployeeRepository $eQEmployeeRepository)
    {
        $this->repository = $eQEmployeeRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'e_q_approval_id' => 'required|integer|exists:e_q_approvals,id',
            'name' => "required|string|max:255",
            'nationality' => "required|string|max:255",
            'position' => "required|string|max:255",
            'category' => "required|string|max:255",
            'years_experience' => "required|integer",
            'start_date' => "required|date",
            'end_date' => "required|date",
            'employee_nature' => "required|string|max:255",
            'educational_qualifications' => "required|string|max:255",
            'professional_qualifications' => "required|string|max:255",
            'job_description' => "required|string|min:5",
        ];
    }
}
