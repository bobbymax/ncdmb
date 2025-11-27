<?php

namespace App\Repositories;

use App\Models\CompanyRepresentative;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CompanyRepresentativeRepository extends BaseRepository
{
    public function __construct(CompanyRepresentative $companyRepresentative) {
        parent::__construct($companyRepresentative);
    }

    public function parse(array $data): array
    {
        return $data;
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $rep = parent::create($data);

            if (!$rep) {
                return null;
            }

            $password = "{$rep->firstname}.{$rep->surname}";

            $user = User::create([
                'staff_no' => "",
                'firstname' => $rep->firstname,
                'middlename' => $rep->other_names ?? null,
                'surname' => $rep->surname,
                'email' => $rep->email,
                'password' => Hash::make($password),
                'department_id' => null,
                'grade_level_id' => null,
                'role_id' => null,
                'default_page_id' => null,
                'type' => 'support',
                'date_joined' => now(),
                'gender' => "female",
            ]);

            if (!$user) {
                return null;
            }

            return $rep;
        });
    }
}
