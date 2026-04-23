<?php
/**
 * Configuration générale de l'application
 */
return [
    'name'     => env('APP_NAME', 'Les éditions Variable'),
    'url'      => env('APP_URL', 'http://localhost:8888'),
    'env'      => env('APP_ENV', 'local'),
    'debug'    => env('APP_DEBUG', 'true') === 'true',
    'timezone' => env('TIMEZONE', 'Africa/Kinshasa'),
];
