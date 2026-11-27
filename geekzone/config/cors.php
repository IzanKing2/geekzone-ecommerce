<?php

/*
|--------------------------------------------------------------------------
| CORS
|--------------------------------------------------------------------------
|
| La web (Blade) y la API comparten origen, así que no hace falta abrir la API
| a otros sitios. Por defecto solo se permite APP_URL; para añadir más
| orígenes (p. ej. un frontend aparte) define CORS_ALLOWED_ORIGINS separados
| por comas. Nunca uses "*" con una API que emite tokens JWT.
|
*/

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

    'allowed_origins' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CORS_ALLOWED_ORIGINS', env('APP_URL', 'http://localhost:8080')))
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization', 'Accept', 'X-Requested-With'],

    'exposed_headers' => [],

    'max_age' => 600,

    'supports_credentials' => false,

];
