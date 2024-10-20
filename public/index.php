<?php define("APP_START", time());

/**
 * Define Global Constants
 */

define("DOCUMENT_ROOT", $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR. '..');
define("SOURCE_ROOT", DOCUMENT_ROOT . DIRECTORY_SEPARATOR . 'src');
define("UPLOADS_PATH", DOCUMENT_ROOT . DIRECTORY_SEPARATOR . 'uploads');
define("ARCHIVE_PATH", DOCUMENT_ROOT . DIRECTORY_SEPARATOR . 'archive');
define("TEMP_PATH", DOCUMENT_ROOT . DIRECTORY_SEPARATOR . 'temp');

// If the uploads, archive and temp directories do not exist, create them
if(!file_exists(UPLOADS_PATH)) {
    mkdir(UPLOADS_PATH);
}
if(!file_exists(ARCHIVE_PATH)) {
    mkdir(ARCHIVE_PATH);
}
if(!file_exists(TEMP_PATH)) {
    mkdir(TEMP_PATH);
}

/**
 * Load the bootstrap file
 */
require_once DOCUMENT_ROOT . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'bootstrap.php';