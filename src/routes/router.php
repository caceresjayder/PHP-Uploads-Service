<?php
namespace src\routes;

use DI\ContainerBuilder;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use src\database\DBConnection;
use src\database\RedisConnection;
use ZipArchive;

// Initialize the container
$containerBuilder = new ContainerBuilder();
// Set up settings
$container = $containerBuilder->build();
// Set up uploads and archive directories
$container->set('upload_directory', UPLOADS_PATH . DIRECTORY_SEPARATOR . config('storage.uploads.path'));
$container->set('archive_directory', ARCHIVE_PATH . DIRECTORY_SEPARATOR . config('storage.archive.path'));


// Create the app
AppFactory::setContainer($container);
$app = AppFactory::create();


$app->get('/health', function (Request $request, Response $response): ResponseInterface 
{
    try {
        // Check if the database and Redis connections are healthy
        return json_response($response, [
            "status" => "ok",
            "database" => DBConnection::ping(),
            "redis" => RedisConnection::ping(),
            "actual_date" => date('Y-m-d H:i:s'),
        ]);

    } catch (\Throwable $e) {
        return json_response($response, [
            "status" => "Service unhealthy",
            "message" => config('app.env') === 'development' ? $e->getMessage() : null,
        ], 400);
    }


});

$app->get('/[{id}]', function (Request $request, Response $response, array $args): ResponseInterface 
{

    try {
        // Get the uploads directory
        $uploadsDirectory = $this->get('upload_directory');
        // Get the archive directory
        $archivesDirectory = $this->get('archive_directory');
        // Get the id parameter
        $ids = $args['id'] ?? $request->getQueryParams()['id'] ?? [];
        // If the id is a string, convert it to an array
        if(gettype($ids) === 'string') {
            $ids = explode(',', $ids);
        }
        // Filter the ids
        $ids = filterIds($ids);

        // If the id is empty, return an error
        if (empty($ids)) {
            return json_response($response, ["message" => "Invalid id"], 400);
        }
        // Get the files
        $uploads = getFiles($ids);
        // If no files were found, return an error
        if (empty($uploads)) {
            return json_response($response, ["message" => "File not found"], 404);
        }
        // If only one file was found, return the file
        if (count($uploads) === 1) {
            // Get the file
            $upload = $uploads[0];
            // Get the file path
            $filePath = findFile(
                $upload['file'],
                $uploadsDirectory, 
                $archivesDirectory);

            // If the file does not exist, return an error
            if (!$filePath) {
                return json_response($response, ["message" => "File not found"], 404);
            }

            // Create a stream
            $stream = new \Slim\Psr7\Stream(fopen($filePath, 'r'));

            // Set the headers
            $response = $response
                ->withHeader('Content-Type', $upload['type'])
                ->withHeader('Content-Disposition', 'attachment; filename=' . $upload['name'])
                ->withHeader('Content-Length', $upload['size']);

            // Return the response
            return $response->withBody($stream);
        }

        // If multiple files were found, create a zip archive
        $file = md5(rand() . uniqid());
        // Create the zip file
        $file = TEMP_PATH . DIRECTORY_SEPARATOR . "{$file}.zip";
        // Create a new zip archive
        $zip = new ZipArchive();
        // Open the zip file
        $zip->open($file, ZipArchive::CREATE);
        // Add the files to the zip archive
        foreach ($uploads as $upload) {
            // Get the file path
            $filePath = findFile(
                $upload['file'],
                $uploadsDirectory, 
                $archivesDirectory);
            // If the file does not exist, skip it
            if (!$filePath) continue;
            // Add the file to the zip archive
            $zip->addFile($filePath, $upload['name']);
        }
        // Close the zip file
        $zip->close();
        // Create a stream
        $stream = new \Slim\Psr7\Stream(fopen($file, 'r'));
        // Delete the zip file from temp path
        unlink($file);
        // Set the headers
        $response = $response
            ->withHeader('Content-Type', 'application/zip')
            ->withHeader('Content-Disposition', 'attachment; filename=uploads.zip')
            ->withHeader('Content-Length', filesize($file));
        // Return the response
        return $response->withBody($stream);

    } catch (Exception $e) {
        // Return an error response
        return json_response($response, [
            "message" => 'Error retrieving file',
            "error" => config('app.debug') ? $e->getMessage() : null,
        ], 400);
    }

});

$app->post('/', function (Request $request, Response $response, array $args): ResponseInterface {

    try {
        // Get the uploads directory
        $uploadsDirectory = $this->get('upload_directory');
        /**
         * @var \Slim\Psr7\UploadedFile[] $uploadedFiles
         */
        $uploadedFiles = $request->getUploadedFiles();
        // Get the request body
        $body = $request->getBody()->getContents();

        // If no files were uploaded, return an error
        if (empty($uploadedFiles)) {
            // If no files were uploaded, check if the file was sent as a base64 string
            if (!isset($body['file'])) {
                // If no file was sent, return an error
                return json_response($response, ["message" => "No files uploaded"], 400);
            }

            // If the file was sent as a base64 string, decode it and save it to a file
            $uploadedFiles = [base64_decode(str_replace(' ', '+', $body['file']))];
        }


        // Validate the uploaded files
        $validation = validate_files($uploadedFiles);
        // If the validation fails, return the error
        if ($validation['valid'] === false) {
            // Return the error response
            return json_response($response, $validation, 400);
        }

        // Move the uploaded files to the uploads directory
        $files = [];
        foreach ($uploadedFiles as $uploadedFile) {
            // Generate a unique filename
            $filename = get_unique_filename($uploadedFile->getClientFilename());
            // Move the file to the uploads directory
            $uploadedFile->moveTo($uploadsDirectory . DIRECTORY_SEPARATOR . $filename);
            // Prepare the file data
            $files[] = [
                'id' => md5($filename),
                'name' => sanitize_filename($uploadedFile->getClientFilename()),
                'file' => $filename,
                'size' => $uploadedFile->getSize(),
                'type' => $uploadedFile->getClientMediaType(),
            ];
        }

        //Prepare the query to insert many uploads
        $query = 'insert into upload (id, 
            name, 
            type, 
            size, 
            file) values ';
        // Prepare the parameters
        $params = [];
        // Loop through the files and prepare the query and parameters
        foreach ($files as $file) {
            // Prepare the query
            $query .= '(:id' . $file['id'] . ', 
                :name' . $file['id'] . ', 
                :type' . $file['id'] . ', 
                :size' . $file['id'] . ', 
                :file' . $file['id'] . '),';
            // Prepare the parameters
            $params['id' . $file['id']] = $file['id'];
            $params['name' . $file['id']] = $file['name'];
            $params['type' . $file['id']] = $file['type'];
            $params['size' . $file['id']] = $file['size'];
            $params['file' . $file['id']] = $file['file'];
        }
        // Remove the trailing comma
        $query = rtrim($query, ',');

        // Insert the files into the database
        if (!DBConnection::insert($query, $params)) {
            // If the insert fails, delete the uploaded files
            foreach ($files as $file) {
                unlink($uploadsDirectory . DIRECTORY_SEPARATOR . $file['file']);
            }
            // Return an error response
            return json_response($response, ["message" => "Error inserting data"], 400);
        }
        // Return a success response
        return json_response($response, $files);

    } catch (Exception $e) {
        return json_response($response, [
            "message" => 'Error uploading file',
            "error" => config('app.debug') ? $e->getMessage() : null,
        ], 400);
    }
});
// Run the app
$app->run();