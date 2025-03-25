<?php

namespace App\Http\Controllers;


use App\Http\Resources\WidgetResource;
use App\Services\WidgetService;

class WidgetController extends BaseController
{
    public function __construct(WidgetService $widgetService) {
        parent::__construct($widgetService, 'Widget', WidgetResource::class);
    }
}
