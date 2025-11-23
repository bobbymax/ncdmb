<?php

namespace App\Console\Commands;

use App\Services\CarderService;
use App\Services\DepartmentService;
use App\Services\GradeLevelService;
use App\Services\GroupService;
use App\Services\LocationService;
use App\Services\RoleService;
use App\Services\SettingService;
use App\Services\UserService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AdministratorSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'administrator:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up initial administrator profile: Super Administrator role, Enterprise Administrators group, ADL1 grade level, Administrator Department, and Arcitect user';

    protected UserService $userService;
    protected RoleService $roleService;
    protected GroupService $groupService;
    protected DepartmentService $departmentService;
    protected GradeLevelService $gradeLevelService;
    protected LocationService $locationService;
    protected CarderService $carderService;
    protected SettingService $settingService;

    public function __construct(
        UserService $userService,
        RoleService $roleService,
        GroupService $groupService,
        DepartmentService $departmentService,
        GradeLevelService $gradeLevelService,
        LocationService $locationService,
        CarderService $carderService,
        SettingService $settingService
    ) {
        parent::__construct();
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->groupService = $groupService;
        $this->departmentService = $departmentService;
        $this->gradeLevelService = $gradeLevelService;
        $this->locationService = $locationService;
        $this->carderService = $carderService;
        $this->settingService = $settingService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('🚀 Starting Administrator Setup...');
        $this->newLine();

        try {
            DB::beginTransaction();

            // Step 1: Create Administrator Department
            $this->info('📁 Creating Administrator Department...');
            $department = $this->createDepartment();
            $this->info("   ✓ Department created: {$department->name}");

            // Step 2: Create Super Administrator Role
            $this->info('👤 Creating Super Administrator Role...');
            $role = $this->createRole($department);
            $this->info("   ✓ Role created: {$role->name}");

            // Step 3: Create Enterprise Administrators Group
            $this->info('👥 Creating Enterprise Administrators Group...');
            $group = $this->createGroup();
            $this->info("   ✓ Group created: {$group->name}");

            // Step 4: Create Carder for Grade Level (with group)
            $this->info('📋 Creating Administrator Carder...');
            $carder = $this->createCarder($group);
            $this->info("   ✓ Carder created: {$carder->name}");

            // Step 5: Create ADL1 Grade Level
            $this->info('📊 Creating ADL1 Grade Level...');
            $gradeLevel = $this->createGradeLevel($carder);
            $this->info("   ✓ Grade Level created: {$gradeLevel->name} ({$gradeLevel->key})");

            // Step 6: Create or get default Location
            $this->info('📍 Setting up Location...');
            $location = $this->getOrCreateLocation();
            $this->info("   ✓ Location ready: {$location->name}");

            // Step 7: Create Arcitect User
            $this->info('👨‍💼 Creating Arcitect Administrator User...');
            $user = $this->createUser($role, $department, $gradeLevel, $location, $group);
            $this->info("   ✓ User created: {$user->firstname} {$user->surname} ({$user->email})");

            // Step 8: Create App State Setting
            $this->info('⚙️  Creating App State Setting...');
            $setting = $this->createAppStateSetting();
            $this->info("   ✓ Setting created: {$setting->name} ({$setting->key})");

            DB::commit();

            $this->newLine();
            $this->info('✅ Administrator setup completed successfully!');
            $this->newLine();
            $this->info('📝 Summary:');
            $this->line("   • Department: {$department->name}");
            $this->line("   • Role: {$role->name}");
            $this->line("   • Group: {$group->name}");
            $this->line("   • Grade Level: {$gradeLevel->name} ({$gradeLevel->key})");
            $this->line("   • User: {$user->firstname} {$user->surname}");
            $this->line("   • Email: {$user->email}");
            $this->line("   • Setting: {$setting->name} = {$setting->value}");
            $this->newLine();
            $this->warn('⚠️  Default password: ' . strtolower($user->firstname) . strtolower($user->surname));
            $this->newLine();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Setup failed: ' . $e->getMessage());
            $this->error('Stack trace: ' . $e->getTraceAsString());
            return;
        }
    }

    /**
     * Create Administrator Department
     */
    private function createDepartment()
    {
        $repository = $this->departmentService->getRepository();
        $existing = $repository->getRecordByColumn('label', 'administrator-department');

        if ($existing) {
            $this->warn('   ⚠ Department already exists, using existing one.');
            return $existing;
        }

        return $this->departmentService->store([
            'name' => 'Administrator Department',
            'abv' => 'ADMD',
            'type' => 'directorate',
            'signatory_staff_id' => 0,
            'alternate_signatory_staff_id' => 0,
        ]);
    }

    /**
     * Create Super Administrator Role
     */
    private function createRole($department)
    {
        $repository = $this->roleService->getRepository();
        $existing = $repository->getRecordByColumn('slug', 'super-administrator');

        if ($existing) {
            $this->warn('   ⚠ Role already exists, using existing one.');
            return $existing;
        }

        return $this->roleService->store([
            'name' => 'Super Administrator',
            'department_id' => $department->id,
            'slots' => 1,
            'access_level' => 'system',
        ]);
    }

    /**
     * Create Enterprise Administrators Group
     */
    private function createGroup()
    {
        $repository = $this->groupService->getRepository();
        $existing = $repository->getRecordByColumn('label', 'enterprise-administrators');

        if ($existing) {
            $this->warn('   ⚠ Group already exists, using existing one.');
            return $existing;
        }

        return $this->groupService->store([
            'name' => 'Enterprise Administrators',
        ]);
    }

    /**
     * Create Carder for Grade Level
     */
    private function createCarder($group)
    {
        $repository = $this->carderService->getRepository();
        $existing = $repository->getRecordByColumn('label', 'administrator-carder');

        if ($existing) {
            $this->warn('   ⚠ Carder already exists, using existing one.');
            // Ensure group is attached
            if (!$existing->groups()->where('groups.id', $group->id)->exists()) {
                $existing->groups()->attach($group->id);
            }
            return $existing;
        }

        return $this->carderService->store([
            'name' => 'Administrator Carder',
            'groups' => [
                ['id' => $group->id]
            ],
        ]);
    }

    /**
     * Create ADL1 Grade Level
     */
    private function createGradeLevel($carder)
    {
        $repository = $this->gradeLevelService->getRepository();
        $existing = $repository->getRecordByColumn('key', 'ADL1');

        if ($existing) {
            $this->warn('   ⚠ Grade Level already exists, using existing one.');
            return $existing;
        }

        return $this->gradeLevelService->store([
            'key' => 'ADL1',
            'name' => 'Administrator Level 1',
            'type' => 'system',
            'carder_id' => $carder->id,
        ]);
    }

    /**
     * Get or create default Location
     */
    private function getOrCreateLocation()
    {
        $repository = $this->locationService->getRepository();
        $existing = $repository->getRecordByColumn('name', 'NCDMB Tower');

        if ($existing) {
            return $existing;
        }

        // Check if we need to create a city first
        // For now, let's try to use the first available city or create a minimal setup
        $city = DB::table('cities')->first();
        
        if (!$city) {
            // If no cities exist, we'll need to handle this
            // For now, let's create a basic location structure
            // Note: This might need adjustment based on your city setup
            $this->warn('   ⚠ No cities found. You may need to set up cities first.');
            $this->warn('   ⚠ Attempting to create location without city_id...');
            
            // Try to create with a default city_id of 1, or handle gracefully
            try {
                return $this->locationService->store([
                    'name' => 'NCDMB Tower',
                    'city_id' => 1, // Default, may need adjustment
                    'address' => 'Yenagoa, Bayelsa State',
                ]);
            } catch (\Exception $e) {
                // If that fails, we'll need to inform the user
                throw new \Exception('Location creation failed. Please ensure cities are set up first, or modify the command to handle this case. Error: ' . $e->getMessage());
            }
        }

        return $this->locationService->store([
            'name' => 'NCDMB Tower',
            'city_id' => $city->id,
            'address' => 'Yenagoa, Bayelsa State',
        ]);
    }

    /**
     * Create Arcitect User
     */
    private function createUser($role, $department, $gradeLevel, $location, $group)
    {
        $repository = $this->userService->getRepository();
        $existing = $repository->getRecordByColumn('email', 'arcitect@ncdmb.gov.ng');

        if (!$existing) {
            $existing = $repository->getRecordByColumn('staff_no', 'ARCITECT');
        }

        if ($existing) {
            $this->warn('   ⚠ User already exists, skipping creation.');
            // Still assign to group if not already assigned
            if (!$existing->groups()->where('groups.id', $group->id)->exists()) {
                $existing->groups()->attach($group->id);
                $this->info("   ✓ User assigned to {$group->name} group");
            }
            return $existing;
        }

        $userData = [
            'staff_no' => 'ARCITECT',
            'firstname' => 'Arcitect',
            'surname' => 'Administrator',
            'middlename' => null,
            'grade_level_id' => $gradeLevel->id,
            'department_id' => $department->id,
            'role_id' => $role->id,
            'location_id' => $location->id,
            'email' => 'arcitect@ncdmb.gov.ng',
            'gender' => 'male',
            'type' => 'admin',
            'job_title' => 'Super Administrator',
            'groups' => [
                ['value' => $group->id]
            ],
        ];

        return $this->userService->store($userData);
    }

    /**
     * Create App State Setting
     */
    private function createAppStateSetting()
    {
        $repository = $this->settingService->getRepository();
        $existing = $repository->getRecordByColumn('key', 'app.state');

        if ($existing) {
            $this->warn('   ⚠ Setting already exists, using existing one.');
            return $existing;
        }

        // Use repository directly to bypass service transformation (which adds 'jolt_' prefix)
        // This allows us to create the setting with the exact key "app.state"
        return $repository->create([
            'key' => 'app.state',
            'name' => 'App State',
            'value' => 'admin-installation',
            'layout' => 12,
            'access_group' => 'admin',
            'input_type' => 'text',
            'input_data_type' => 'string',
            'order' => 999,
            'is_disabled' => false,
        ]);
    }
}

