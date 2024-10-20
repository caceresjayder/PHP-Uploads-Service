<?php

/**
 * Get an environment variable
 */

use Psr\Http\Message\ResponseInterface;
use src\database\DBConnection;
use src\database\RedisConnection;

if (!function_exists('env')) {
    /**
     * Find an environment variable or return a default value
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    function env(string $key, string|null $default = null): mixed
    {
        // Get the environment variable
        $env = isset($_ENV[$key]) ? trim($_ENV[$key]) : '';
        // Return the environment variable or the default value
        $value = strlen($env) ? $env : ($default ?? null);
        // Return the value
        return $value;
    }
}

/**
 * Get a configuration value
 */
if (!function_exists('config')) {
    /**
     * Get a configuration value
     * @param string $key
     * @param string|null $default
     * @return mixed
     */
    function config(string $key, string|null $default = null): mixed
    {
        // Get the configuration from the config file
        $config = include SOURCE_ROOT . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
        // Get the keys
        $keys = explode('.', $key);
        // Get the value
        foreach ($keys as $key) {
            // If the key exists, get the value
            if (isset($config[$key])) {
                // Set the value
                $config = $config[$key];
            } else {
                return $default;
            }
        }
        // If the value is a truthy string, convert it to a boolean
        if (in_array($config, ['true', '1'])) {
            return true;
        }
        // If the value is a falsy string, convert it to a boolean
        if (in_array($config, ['false', '0'])) {
            return false;
        }
        // Return the value
        return $config;
    }
}

if (!function_exists('validate_files')) {
    /**
     * Summary of validate_files
     * @param \Slim\Psr7\UploadedFile[] $files
     * @return array
     */
    function validate_files(array $files): array
    {
        // Initialize the validation array
        $validation = [
            'valid' => true,
            'errors' => []
        ];


        // Get the supported media files
        $supportedMediaFiles = config('files.supported');
        $maxFiles = config('files.max_files');
        $maxSize = config('files.max_size');

        // Check if the files array exceeds the limit
        if (count($files) > $maxFiles) {
            $validation['valid'] = false;
            $validation['errors'][] = "Max 5 files allowed";
            return $validation;
        }


        // Validate the files
        foreach ($files as $uploadedFile) {
            // Check if the file hasn't an error 
            if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                $validation['valid'] = false;
                $validation['errors'][] = [
                    "message" => "Error uploading file",
                    'filename' => $uploadedFile->getClientFilename(),
                ];
            }
            // Check if the file is too large
            if ($uploadedFile->getSize() > $maxSize) {
                $validation['valid'] = false;
                $validation['errors'][] = [
                    "message" => "File too large (max ". formatBytes($maxSize) . ")",
                    'filename' => $uploadedFile->getClientFilename(),
                ];
            }
            // Check if the file type is supported
            if (!in_array($uploadedFile->getClientMediaType(), $supportedMediaFiles)) {
                $validation['valid'] = false;
                $validation['errors'][] = [
                    "message" => "File type not supported",
                    'filename' => $uploadedFile->getClientFilename(),
                ];
            }
            // Stop the loop if the validation is invalid
            if ($validation['valid'] === false) {
                return $validation;
            }

        }
        // Return the validation
        return $validation;
    }
}

if (!function_exists('sanitize_filename')) {
    /**
     * Sanitize a filename
     * @param string $filename
     * @return string|null
     */
    function sanitize_filename(string $filename): string|null
    {
        return preg_replace('/[^a-zA-Z0-9\.\_\-]/', '-', $filename);
    }
}

if (!function_exists('get_unique_filename')) {
    /**
     * Get a unique filename
     * @param string $originalFileName
     * @return string
     */
    function get_unique_filename(string $originalFileName): string
    {
        // Get the filename
        $filename = pathinfo($originalFileName, PATHINFO_FILENAME);
        // Get the extension
        $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
        // Return the unique filename
        return uniqid() . date("YmdHis") . '_' . sanitize_filename($filename) . '.' . $extension;
    }
}

if (!function_exists('json_response')) {
    /**
     * Return a JSON response
     * @param Psr\Http\Message\ResponseInterface $response
     * @param mixed $data
     * @param mixed $status
     * @return Psr\Http\Message\ResponseInterface
     */
    function json_response(ResponseInterface &$response, $data, $status = 200): ResponseInterface
    {
        $response
            ->getBody()
            ->write(json_encode($data));

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}

if (!function_exists('filterIds')) {
    /**
     * Filter an array of IDs
     * @param array $ids
     * @return array
     */
    function filterIds(array $ids): array
    {
        // If the IDs is not an array, return an empty array
        return array_unique(array_filter($ids, fn($id) => preg_match('/^[a-f0-9]{32}$/', $id)));
    }
}

if (!function_exists('findFile')) {

    /**
     * Find the file in the uploads or archive directory
     * @param string $file
     * @param string $uploadDirectory
     * @param string $archiveDirectory
     * @return string|null
     */
    function findFile(string $file, string $uploadDirectory, string $archiveDirectory): string|null
    {
        // Check if the file exists in the uploads directory
        if (!file_exists($uploadDirectory . DIRECTORY_SEPARATOR . $file)) {
            // Check if the file exists in the archive directory
            if (!file_exists($archiveDirectory . DIRECTORY_SEPARATOR . $file)) {
                return null;
            }
            // Return the file path in the archive directory
            return $archiveDirectory . DIRECTORY_SEPARATOR . $file;
        }
        // Return the file path in the uploads directory
        return $uploadDirectory . DIRECTORY_SEPARATOR . $file;
    }
}

if (!function_exists('getFiles')) {


    /**
     * Get the files from Redis or the database
     * @param array $ids
     * @return array
     */
    function getFiles(array $ids): array
    {
        // Prepare the files array
        $toSearch = [];
        // Prepare the uploads array
        $uploads = [];
        // Verify if the Redis connection is active
        if (RedisConnection::ping()) {
            // Loop through the ids
            foreach ($ids as $id) {
                // Get the file from Redis
                $upload = RedisConnection::get($id);
                // If the file was found, add it to the uploads array
                if ($upload) {
                    // Add the file to the uploads array
                    $uploads[] = $upload;
                }
                // If the file was not found, add the id to the toSearch array
                else {
                    // Add the id to the toSearch array
                    $toSearch[] = $id;
                }
            }
            // If there are ids to search, search them
            if (!empty($toSearch)) {
                // Search the ids in the database
                $found = searchIds($toSearch);
                // If files were found, add them to the uploads array
                if (!empty($found)) {
                    // Loop through the found files
                    foreach ($found as $file) {
                        // Add the file to the uploads array
                        $uploads[] = $file;
                        // Add the file to Redis
                        RedisConnection::set($file['id'], $file);
                    }
                }
            }
        }
        // If the Redis connection is not active, search the ids in the database
        else {
            // Search the ids in the database
            $uploads = searchIds($ids);
        }

        // Return the uploads
        return $uploads;
    }

}


if (!function_exists('searchIds')) {

    /**
     * Search for the files in the database
     * @param mixed $ids
     * @return array
     */
    function searchIds($ids): array
    {
        // Prepare the query
        $query = 'select id,file,name,size,type from upload where id in (';
        // Prepare the update query
        $updateQuery = 'update upload set last_read = :last_read where id in (';
        // Prepare the data array
        $data = [];
        // Loop through the unique ids
        foreach (array_unique($ids) as $k => $id) {
            // Add the id to the query
            $query .= ':id' . $k . ',';
            // Add the id to the update query
            $updateQuery .= ':id' . $k . ',';
            // Add the id to the data array
            $data[':id' . $k] = $id;
        }
        // Remove the trailing comma from the query
        $query = rtrim($query, ',') . ')';
        // Remove the trailing comma from the update query
        $updateQuery = rtrim($updateQuery, ',') . ')';
        // Get the uploads
        $uploads = DBConnection::fetchAll($query, $data);
        // Update the last read date
        if (!empty($uploads)) {
            // Add the last read date to the data array
            $data[':last_read'] = date('Y-m-d H:i:s');
            // Update the last read date
            DBConnection::update($updateQuery, $data);
        }
        // Return the uploads
        return $uploads;
    }
}

if(!function_exists('formatBytes')) {
    function formatBytes($bytes, $precision = 2) { 
        $units = ['B', 'KB', 'MB', 'GB', 'TB']; 
       
        $bytes = max($bytes, 0); 
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
        $pow = min($pow, count($units) - 1); 
       
        // Uncomment one of the following alternatives
        // $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow)); 
       
        return round($bytes, $precision) . $units[$pow]; 
    } 
}

