<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*', 'broadcasting/auth', 'threads/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['http://localhost:3000', 'https://portal.test'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['X-XSRF-TOKEN', 'X-CSRF-TOKEN', 'X-Identity-Marker', 'X-Encrypted', 'X-Frontend-URL', 'X-User-Agent', 'X-Platform', 'X-Screen-Size', 'Content-Type', 'Authorization'],

    'exposed_headers' => ['XSRF-TOKEN'], // Ensures XSRF token is exposed

    'max_age' => 0,

    'supports_credentials' => true,

];
