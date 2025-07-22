<?php

namespace App\Http\Controllers;


use App\Http\Resources\ThresholdResource;
use App\Services\ThresholdService;

class ThresholdController extends BaseController
{
    public function __construct(ThresholdService $thresholdService) {
        parent::__construct($thresholdService, 'Threshold', ThresholdResource::class);
    }
}
