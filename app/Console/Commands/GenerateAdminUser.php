<?php

namespace App\Console\Commands;

use App\Helpers\PackContainer;
use App\Services\DepartmentService;
use App\Services\GradeLevelService;
use App\Services\LocationService;
use App\Services\PageService;
use App\Services\RoleService;
use App\Services\UserService;
use Illuminate\Console\Command;

class GenerateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    protected UserService $userService;
    protected RoleService $roleService;
    protected PageService $pageService;
    protected DepartmentService $departmentService;
    protected GradeLevelService $gradeLevelService;
    protected LocationService $locationService;

    public function __construct(
        UserService $userService,
        RoleService $roleService,
        DepartmentService $departmentService,
        GradeLevelService $gradeLevelService,
        PageService $pageService,
        LocationService $locationService
    ) {
        parent::__construct();
        $this->userService = $userService;
        $this->roleService = $roleService;
        $this->departmentService = $departmentService;
        $this->gradeLevelService = $gradeLevelService;
        $this->pageService = $pageService;
        $this->locationService = $locationService;
    }

    /**
     * Execute the console command.
     * @throws \Exception
     */
    public function handle(): void
    {
        $this->info('Creating Admin Role...');
        $role = $this->roleService->store(PackContainer::role());
        $this->info('Creating Admin Department...');
        $department = $this->departmentService->store(PackContainer::department());
        $this->info('Creating Admin Grade Level...');
        $gradeLevel = $this->gradeLevelService->store(PackContainer::grade());
        $this->info('Creating Admin Location...');
        $location = $this->locationService->store(PackContainer::location());
        $this->info('Creating Admin User...');
        $this->userService->store(PackContainer::admin($role, $location, $department, $gradeLevel));
        $this->info('Creating Pages...');

        $roles = [
            [
                "value" => $role->id,
                "label" => $role->name
            ]
        ];

        foreach(PackContainer::pages($roles) as $page) {
            $this->pageService->store($page);
        }

        $this->info('All Done...');
        $this->info('Build something amazing...');
    }
}
