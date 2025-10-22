<?php

namespace App\Services;

use App\Repositories\DocumentPanelRepository;
use App\Repositories\GroupRepository;
use Illuminate\Support\Facades\DB;

class DocumentPanelService extends BaseService
{
    protected GroupRepository $groupRepository;
    public function __construct(DocumentPanelRepository $documentPanelRepository, GroupRepository $groupRepository)
    {
        parent::__construct($documentPanelRepository);
        $this->groupRepository = $groupRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'name' => 'required|string|max:255',
            'icon' => 'required|string|max:255',
            'component_path' => 'required|string|max:255',
            'order' => 'required|integer|min:1',
            'is_active' => 'required|boolean',
            'is_editor_only' => 'required|boolean',
            'is_view_only' => 'required|boolean',
            'visibility_mode' => 'required|string|max:255|in:both,preview,editor',
            'is_global' => 'required|boolean',
            'groups' => 'required|array',
            'groups.*.value' => 'required|integer|exists:groups,id',
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            $panel = parent::store($data);

            if (!$panel) {
                return null;
            }

            $groupIds = collect($data['groups'])->pluck('value')->toArray();
            $panel->groups()->sync($groupIds);

            return $panel;
        });
    }

    public function update(int $id, array $data, $parsed = true)
    {
        return DB::transaction(function () use ($id, $data, $parsed) {
            $panel = parent::update($id, $data);

            if (!$panel) {
                return null;
            }

            $groupIds = collect($data['groups'])->pluck('value')->toArray();
            $panel->groups()->sync($groupIds);

            return $panel;
        });
    }
}
