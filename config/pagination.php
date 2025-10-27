<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Pagination Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default pagination settings for different resource types.
    |
    */

    'documents_per_page' => env('DOCUMENTS_PER_PAGE', 50),
    'default_per_page' => env('DEFAULT_PER_PAGE', 25),
    'max_per_page' => env('MAX_PER_PAGE', 100),
];

