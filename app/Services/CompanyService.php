<?php

namespace App\Services;

use App\Repositories\CompanyRepository;
use App\Repositories\CompanyRepresentativeRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CompanyService extends BaseService
{
    protected CompanyRepresentativeRepository $companyRepresentativeRepository;
    public function __construct(CompanyRepository $companyRepository, CompanyRepresentativeRepository $companyRepresentativeRepository)
    {
        parent::__construct($companyRepository);
        $this->companyRepresentativeRepository = $companyRepresentativeRepository;
    }

    public function rules($action = "store"): array
    {
        // Base rules that apply to both store and update
        $rules = [
            'user_id' => 'nullable|integer|exists:users,id',
            'name' => 'required|string|max:255',
            'logo' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'ncec_number' => 'nullable|string|max:255',
            'nogicjqs_certificate_number' => 'nullable|string|max:255',
            'categorization_status' => 'nullable|string|max:255',
            'is_blocked' => 'nullable|boolean',

            // Representative validation (optional, but if provided, validate all fields)
            'representative' => 'nullable|array',
            'representative.firstname' => 'required_with:representative|string|max:255',
            'representative.middlename' => 'nullable|string|max:255',
            'representative.surname' => 'required_with:representative|string|max:255',
            'representative.email' => 'required_with:representative|email|max:255',
            'representative.phone' => 'required_with:representative|string|max:255',
        ];

        // Action-specific unique constraints
        if ($action === "store") {
            // On create: enforce uniqueness (Laravel automatically ignores soft-deleted records)
            $rules['email'] .= '|unique:companies,email';
            $rules['ncec_number'] .= '|unique:companies,ncec_number';
            $rules['nogicjqs_certificate_number'] .= '|unique:companies,nogicjqs_certificate_number';

            // Representative email must be unique in company_representatives table
            $rules['representative.email'] .= '|unique:company_representatives,email';
        } else if ($action === "update") {
            // On update: ignore current record for unique constraints
            // Laravel automatically handles soft-deleted records with Rule::unique()
            // Get the ID from route parameter (could be 'company' or 'id' depending on route definition)
            $companyId = request()->route('company')
                ?? request()->route('id')
                ?? request()->id;

            $rules['email'] = [
                'nullable',
                'email',
                'max:255',
                Rule::unique('companies', 'email')->ignore($companyId),
            ];

            $rules['ncec_number'] = [
                'nullable',
                'string',
                'max:255',
                Rule::unique('companies', 'ncec_number')->ignore($companyId),
            ];

            $rules['nogicjqs_certificate_number'] = [
                'nullable',
                'string',
                'max:255',
                Rule::unique('companies', 'nogicjqs_certificate_number')->ignore($companyId),
            ];
        }

        return $rules;
    }

    /**
     * Store a new company and optionally create the principal representative
     *
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Extract representative data if present
            $representativeData = $data['representative'] ?? null;
            
            // Remove representative from company data
            unset($data['representative']);

            // Prepend https:// to website if it doesn't have a protocol
            if (!empty($data['website']) && !preg_match('/^https?:\/\//', $data['website'])) {
                $data['website'] = 'https://' . $data['website'];
            }

            // Create the company
            $company = parent::store($data);

            if (!$company) {
                throw new \Exception('Failed to create company');
            }

            // If representative data is provided, create the principal representative
            if ($representativeData && is_array($representativeData)) {
                $this->companyRepresentativeRepository->create([
                    'company_id' => $company->id,
                    'firstname' => $representativeData['firstname'],
                    'surname' => $representativeData['surname'],
                    'other_names' => $representativeData['middlename'] ?? null,
                    'email' => $representativeData['email'],
                    'phone' => $representativeData['phone'] ?? null,
                    'gender' => 'other', // Default gender, can be updated later
                    'category' => 'principal', // First representative is always principal
                    'is_active' => true, // Principal representative should be active
                ]);
            }

            return $company;
        });
    }

    /**
     * Update an existing company
     *
     * @param int $id
     * @param array $data
     * @param bool $parsed
     * @return mixed
     * @throws \Exception
     */
    public function update(int $id, array $data, $parsed = true)
    {
        // Prepend https:// to website if it doesn't have a protocol
        if (!empty($data['website']) && !preg_match('/^https?:\/\//', $data['website'])) {
            $data['website'] = 'https://' . $data['website'];
        }

        return parent::update($id, $data, $parsed);
    }
}
