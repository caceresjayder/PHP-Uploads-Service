<?php require_once DOCUMENT_ROOT . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

/**
 * Load the environment variables
 */
$dotenv = Dotenv\Dotenv::createImmutable(DOCUMENT_ROOT);
$dotenv->load();
/**
 * VErifies existence of the required environment variables
 */
$dotenv->required([
    'DB_HOST', 
    'DB_DATABASE', 
    'DB_USERNAME', 
    'DB_PASSWORD',
    'APP_ENV',
    'APP_DEBUG',
    'APP_URL',
]);

/**
 * Load the configuration files
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'utils' . DIRECTORY_SEPARATOR . 'functions.php';
date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));


foreach (glob(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . '*.php') as $configFile) {
    require_once $configFile;
}
foreach (glob(__DIR__ . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . '*.php') as $configFile) {
    require_once $configFile;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'router.php';