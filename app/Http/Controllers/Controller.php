<?php

namespace App\Http\Controllers;

use App\Handlers\DataNotFound;
use App\Handlers\RecordCreationUnsuccessful;
use App\Handlers\ValidationErrors;
use App\Services\BaseService;
use App\Traits\ApiResponse;
use App\Traits\ResourceContainer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;

abstract class Controller
{
    use ApiResponse, ResourceContainer;
}
