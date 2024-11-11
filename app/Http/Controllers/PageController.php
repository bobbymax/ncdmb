<?php

namespace App\Http\Controllers;

use App\Services\PageService;

class PageController extends Controller
{
    public function __construct(PageService $pageService) {
        $this->service = $pageService;
        $this->name = 'Page';
    }
}
