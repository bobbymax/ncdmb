<?php

namespace App\Services;

use App\Repositories\CarderRepository;
use App\Repositories\GroupRepository;
use App\Traits\ServiceAction;
use Illuminate\Support\Facades\DB;

class CarderService extends BaseService
{
    use ServiceAction;

    protected GroupRepository $groupRepository;
    public function __construct(CarderRepository $carderRepository, GroupRepository $groupRepository)
    {
        parent::__construct($carderRepository);
        $this->groupRepository = $groupRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'groups' => 'required|array',
            'groups.*.id' => 'required|integer|exists:groups,id',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $carder = parent::store($data);

            if (!$carder) {
                return null;
            }

            foreach ($data['groups'] as $obj) {
                $group = $this->groupRepository->find($obj['id']);

                if ($group) {
                    $carder->groups()->save($group);
                }
            }

            return $carder;
        });
    }

    public function update(int $id, array $data, $parsed = true)
    {
        return DB::transaction(function () use ($id, $parsed, $data) {
            $carder = parent::update($id, $data);
            if (!$carder) {
                return null;
            }

            // Find IDs that exist in the backend but NOT in the frontend (to delete)
            $groupsIds = $carder->groups->pluck('id')->toArray();
            $toDelete = $this->handleIdDeletions($data['groups'], 'id', $groupsIds);

            foreach ($data['groups'] as $obj) {
                $group = $this->groupRepository->find($obj['id']);

                if ($group && !in_array($group->id, $groupsIds)) {
                    $carder->groups()->save($group);
                }
            }

            if (!empty($toDelete)) {
                foreach ($toDelete as $id) {
                    $deletedGroup = $this->groupRepository->find($id);

                    if ($deletedGroup) {
                        $carder->groups()->detach($deletedGroup);
                    }
                }
            }

            return $carder;
        });
    }
}
