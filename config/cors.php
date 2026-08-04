<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['https://bisondenimcollection.com','https://admin.bisondenimcollection.com','http://localhost:3000', 'http://localhost:3001', 'https://bison-denim-frontend.vercel.app', 'https://bison-denim-cms.vercel.app'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
