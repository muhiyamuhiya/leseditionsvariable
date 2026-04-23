<?php
/**
 * Configuration de la base de données
 * Les valeurs sont lues depuis le fichier .env
 */
return [
    'host'     => env('DB_HOST', 'localhost'),
    'port'     => env('DB_PORT', '8889'),
    'name'     => env('DB_NAME', 'leseditionsvariable'),
    'user'     => env('DB_USER', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset'  => 'utf8mb4',
];
