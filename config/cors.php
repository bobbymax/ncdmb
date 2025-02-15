<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:3000'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['X-XSRF-TOKEN', 'X-CSRF-TOKEN', 'Content-Type', 'Authorization'],

    'exposed_headers' => ['XSRF-TOKEN'], // Ensures XSRF token is exposed

    'max_age' => 0,

    'supports_credentials' => true,

];
