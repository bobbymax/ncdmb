<?php

namespace App\Services;

use App\Models\Widget;
use App\Repositories\GroupRepository;
use App\Repositories\WidgetRepository;
use App\Traits\ServiceAction;
use Illuminate\Support\Facades\DB;

class WidgetService extends BaseService
{
    use ServiceAction;

    protected GroupRepository $groupRepository;

    public function __construct(WidgetRepository $widgetRepository, GroupRepository $groupRepository)
    {
        parent::__construct($widgetRepository);
        $this->groupRepository = $groupRepository;
    }

    public function rules($action = "store"): array
    {
        return [
            'document_type_id' => 'required|integer|exists:document_types,id',
            'department_id' => 'sometimes|nullable|integer',
            'title' => 'required|string|max:255',
            'component' => 'required|string|max:255',
            'is_active' => 'sometimes|integer|in:0,1',
            'response' =>  'required|string|in:resource,collection',
            'type' => 'required|string|in:box,card,chart,banner,breadcrumb',
            'chart_type' => 'sometimes|nullable|max:255',
            'groups' => ['required', 'array'],
            'groups.*' => ['required', 'array'],
            'groups.*.value' => ['required', 'integer', 'exists:groups,id'],
        ];
    }

    public function store(array $data)
    {
        return DB::transaction(function () use ($data) {
            /** @var Widget|null $widget */
            $widget = parent::store($data);

            if (!$widget) {
                return null;
            }

            $this->syncGroups($widget, $data['groups']);

            return $widget;
        });
    }

    public function update(int $id, array $data, $parsed = true)
    {
        return DB::transaction(function () use ($id, $data, $parsed) {
            /** @var Widget|null $widget */
            $widget = parent::update($id, $data, $parsed);

            if (!$widget) {
                return null;
            }

            $this->syncGroups($widget, $data['groups']);

            return $widget;
        });
    }

    protected function syncGroups(Widget $widget, array $groups): void
    {
        $groupIds = collect($groups)->pluck('value')->unique()->toArray();
        $widget->groups()->sync($groupIds);
    }
}
