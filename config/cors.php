<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración de CORS (Cross-Origin Resource Sharing)
    |--------------------------------------------------------------------------
    |
    | Aquí se configuran los ajustes para compartir recursos entre orígenes.
    | Esto determina qué operaciones entre orígenes pueden ejecutarse
    | en navegadores web.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // Permitir todas las orígenes por ahora (cambiar en producción)
    // 'allowed_origins' => ['*'],
    'allowed_origins' => array_filter([env('FRONTEND_URL')]),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
