<?php

namespace App\Http\Controllers;

use App\Services\ResearchLibraryService;

class ResearchLibraryController extends Controller
{
    public function __construct(ResearchLibraryService $researchLibraryService) {
        $this->service = $researchLibraryService;
        $this->name = 'ResearchLibrary';
    }
}
