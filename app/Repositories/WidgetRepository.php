<?php

namespace App\Repositories;

use App\Models\Widget;

class WidgetRepository extends BaseRepository
{
    public function __construct(Widget $widget) {
        parent::__construct($widget);
    }

    public function parse(array $data): array
    {
        return $data;
    }
}
