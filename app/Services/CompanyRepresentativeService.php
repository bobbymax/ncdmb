<?php

namespace App\Services;

use App\Repositories\CompanyRepresentativeRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class CompanyRepresentativeService extends BaseService
{
    public function __construct(CompanyRepresentativeRepository $companyRepresentativeRepository)
    {
        parent::__construct($companyRepresentativeRepository);
    }

    public function rules($action = "store"): array
    {
        // Base rules that apply to both store and update
        $rules = [
            'user_id' => 'nullable|integer|exists:users,id',
            'company_id' => 'nullable|integer|exists:companies,id',
            'firstname' => 'required|string|max:255',
            'surname' => 'required|string|max:255',
            'other_names' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:255',
            'gender' => 'required|string|in:male,female,other',
            'category' => 'required|string|in:principal,rep',
            'is_active' => 'nullable|boolean',
        ];

        // Action-specific unique constraints
        if ($action === "store") {
            // On create: enforce uniqueness (Laravel automatically ignores soft-deleted records)
            $rules['email'] .= '|unique:company_representatives,email';
        } else if ($action === "update") {
            // On update: ignore current record for unique constraints
            // Laravel automatically handles soft-deleted records with Rule::unique()
            // Get the ID from route parameter (could be 'companyRepresentative' or 'id' depending on route definition)
            $representativeId = request()->route('companyRepresentative')
                ?? request()->route('company_representative')
                ?? request()->route('id')
                ?? request()->id;

            $rules['email'] = [
                'required',
                'email',
                'max:255',
                Rule::unique('company_representatives', 'email')->ignore($representativeId),
            ];
        }

        return $rules;
    }
}
