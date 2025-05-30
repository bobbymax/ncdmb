<?php

namespace App\Services;


use App\Engine\Puzzle;
use App\Repositories\PageRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PageService extends BaseService
{
    protected PermissionRepository $permissionRepository;
    protected RoleRepository $roleRepository;
    private $bread = ["Browse", "Read", "Add", "Edit", "Delete"];
    public function __construct(
        PageRepository $pageRepository,
        PermissionRepository $permissionRepository,
        RoleRepository $roleRepository
    ) {
        parent::__construct($pageRepository);
        $this->permissionRepository = $permissionRepository;
        $this->roleRepository = $roleRepository;
    }

    public function rules($action = "store"): array
    {
        $rules = [
            'parent_id' => 'required|integer|min:0',
            'workflow_id' => 'sometimes|integer|min:0',
            'document_type_id' => 'sometimes|integer|min:0',
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:255',
            'icon' => 'nullable|string|max:255',
            'type' => 'required|string|max:255|in:app,index,view,form,external,dashboard,report',
            'description' => 'nullable|string|min:5',
            'meta_data' => 'nullable|string',
            'image_path' => 'nullable',
            'roles' => 'required|array',
            'is_menu' => 'sometimes|nullable|boolean',
            'is_disabled' => 'sometimes|nullable|boolean',
            'is_default' => 'sometimes|nullable|boolean',
        ];

        if ($action == "store") {
            $rules['path'] .= '|unique:pages';
        }

        return $rules;
    }

    private function getPermissions($moduleName): array
    {
        $permissions = [];

        foreach ($this->bread as $bread) {
            $permissions[] = "$bread $moduleName";
        }

        return $permissions;
    }

    private function storePermission($page, $permission): void
    {
        $this->permissionRepository->create([
            'page_id' => $page->id,
            'name' => $permission,
        ]);
    }

    /**
     * @throws \Exception
     */
    private function rolePageAccess($page, $record): void
    {
        $role = $this->roleRepository->find($record['value']);

        if ($role && !in_array($role->id, $page->roles->pluck('id')->toArray())) {
            $page->roles()->save($role);
        }
    }

    protected function scrambleImage($image): string
    {
        return Puzzle::scramble($image, Auth::user()->staff_no);
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $image = $data['image_path'] ?? null;

            $page = parent::store([
                ...$data,
                'image_path' => $image ? $this->scrambleImage($image) : null,
            ]);

            if ($page) {
                foreach ($this->getPermissions($page->name) as $permission) {
                    $this->storePermission($page, $permission);
                }

                foreach ($data['roles'] as $record) {
                    $this->rolePageAccess($page, $record);
                }
            }

            return $page;
        });
    }

    public function update(int $id, array $data, $parsed = true)
    {
        return DB::transaction(function () use ($id, $data) {
            $image = $data['image_path'] ?? null;
            // Get previous record
            $record = $this->repository->find($id);
            // Save Previous record name
            $name = $record->label;
            // Update Record
            $page = parent::update($id, [
                ...$data,
                'image_path' => $image ? $this->scrambleImage($image) : null,
            ]);

            if ($page) {
                // Check if the previously stored name is not equal to the updated name
                if ($page->label !== $name) {
                    $page->permissions()->delete();

                    foreach ($this->getPermissions($page->name) as $permission) {
                        $this->storePermission($page, $permission);
                    }
                }

                foreach ($data['roles'] as $record) {
                    $this->rolePageAccess($page, $record);
                }
            }

            return $page;
        });
    }
}
