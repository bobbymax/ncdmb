<?php

namespace App\Http\Controllers;

use App\Services\RenderedServiceService;

class RenderedServiceController extends Controller
{
    public function __construct(RenderedServiceService $renderedServiceService) {
        $this->service = $renderedServiceService;
        $this->name = 'RenderedService';
    }
}
