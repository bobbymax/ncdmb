<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class PackContainer
{
    public static function grade(): array
    {
        return [
            'key' => 'SA01',
            'name' => 'Administrator',
            'type' => 'board'
        ];
    }

    public static function role($department): array
    {
        return [
            'name' => 'Enterprise Administrator',
            'slots' => 1,
            'department_id' => $department?->id,
        ];
    }

    public static function department(): array
    {
        return [
            'name' => 'Administrators',
            'abv' => 'ADMD',
            'type' => 'directorate',
        ];
    }

    public static function location(): array
    {
        return [
            'name' => 'NCDMB Tower',
            'state' => 'Bayelsa',
            'city' => 'Yenagoa'
        ];
    }

    public static function admin($role, $location, $department, $gradeLevel): array
    {
        return [
            'staff_no' => 'ADMIN',
            'firstname' => 'Adiah',
            'surname' => 'Ekaro',
            'middlename' => 'Priye',
            'grade_level_id' => $gradeLevel?->id,
            'department_id' => $department?->id,
            'location_id' => $location?->id,
            'role_id' => $role?->id,
            'email' => 'admin@ncdmb.gov.ng',
            'password' => Hash::make('password'),
            'date_joined' => Carbon::parse('2018-08-06'),
            'gender' => 'female',
            'type' => 'admin',
            'job_title' => 'Enterprise System Administrator',
            'default_page_id' => 1
        ];
    }

    public static function pages(array $roles): array
    {
        return [
            ['name' => 'Admin Centre', 'icon' => 'ri-settings-3-line', 'path' => '/admin-centre', 'type' => 'app', 'roles' => $roles, 'is_menu' => true, 'parent_id' => 0, 'is_default' => true],
            ['name' => 'Pages', 'icon' => 'ri-pages-line', 'path' => '/admin-centre/pages', 'type' => 'index', 'roles' => $roles, 'is_menu' => true, 'parent_id' => 1],
            ['name' => 'Roles', 'icon' => 'ri-admin-line', 'path' => '/admin-centre/roles', 'type' => 'index', 'roles' => $roles, 'is_menu' => true, 'parent_id' => 1],
            ['name' => 'Departments', 'icon' => 'ri-building-4-line', 'path' => '/admin-centre/departments', 'type' => 'index', 'roles' => $roles, 'is_menu' => true, 'parent_id' => 1],
            ['name' => 'Grade Levels', 'icon' => 'ri-stairs-line', 'path' => '/admin-centre/grade-levels', 'type' => 'index', 'roles' => $roles, 'is_menu' => true, 'parent_id' => 1],
            ['name' => 'Employees', 'icon' => 'ri-group-line', 'path' => '/admin-centre/employees', 'type' => 'index', 'roles' => $roles, 'is_menu' => true, 'parent_id' => 1],
        ];
    }
}
