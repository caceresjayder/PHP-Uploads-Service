<?php
return [
    'database' => [
        'main' => [
            'host' => env('DB_HOST', 'localhost'),
            'database' => env('DB_DATABASE', 'test'),
            'username' => env('DB_USERNAME', ''),
            'password' => env('DB_PASSWORD', ''),
            'port' => env('DB_PORT', 3306),
            'driver' => env('DB_DRIVER', 'mysql'),
        ],
    ],
    'redis' => [
        'host' => env('REDIS_HOST', 'localhost'),
        'port' => env('REDIS_PORT', 6379),
        'password' => env('REDIS_PASSWORD', ''),
    ],
    'storage' => [
        'local' => [
            'path' => env('STORAGE_LOCAL_PATH', 'storage'),
        ],
        'uploads' => [
            'path' => env('STORAGE_UPLOADS_PATH', ''),
        ],
        'archive' => [
            'path' => env('STORAGE_ARCHIVE_PATH', ''),
        ]
    ],
    'app' => [
        'env' => env('APP_ENV', 'production'),
        'name' => env('APP_NAME', 'App'),
        'debug' => env('APP_DEBUG', false),
        'url' => env('APP_URL', 'http://localhost'),
        'throw_unhealthy_services' => env('APP_THROW_UNHEALTHY_SERVICES', false),
        'timezone' => env('APP_TIMEZONE', 'UTC'),
    ],
    'files' => [
        // Common empresarial files types
        'supported' => [
            'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'text/plain', 'text/csv', 'application/rtf', 'application/vnd.oasis.opendocument.text',
            'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.oasis.opendocument.presentation',
            'image/jpeg', 'image/jpg','image/png', 'image/gif', 'image/bmp', 'image/webp', 'image/tiff', 'image/svg+xml',
            'audio/mpeg', 'audio/wav', 'video/mp4', 'video/x-msvideo', 'video/quicktime', 'video/x-ms-wmv',
            'video/x-flv', 'video/x-matroska', 'video/webm', 'audio/ogg', 'audio/mp4', 'audio/mp3', 'audio/aac',
            'audio/flac', 'video/3gpp', 'video/x-m4v', 'video/mpeg'
        ],
        // The maximum file size in bytes
        'max_size' => env('FILES_MAX_SIZE', 5000000),
        // The maximum number of files that can be uploaded
        'max_files' => env('FILES_MAX_FILES', 5),
    ]
];