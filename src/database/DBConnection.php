<?php
namespace src\database;

use PDO;
use PDOStatement;

/**
 * Class DBConnection - A class to manage the database connection
 * Singleton pattern
 * @package src\database
 * @version 1.0
 * @since 1.0
 * @see PDO
 */
class DBConnection
{
    /**
     * The database connection
     * @var PDO
     */
    private static PDO $connection;

    /**
     * Get the database connection
     * @return \PDO
     */
    public static function getInstance(): PDO
    {
        // If the connection is not set, create it
        if (!isset(self::$connection)) {
            try {
                // Get the database configuration
                $driver = config('database.main.driver');
                $host = config('database.main.host');
                $database = config('database.main.database');
                $username = config('database.main.username');
                $password = config('database.main.password');
                $port = config('database.main.port');
                // Create the connection
                self::$connection = new PDO("$driver:host=$host;port=$port;dbname=$database", $username, $password);
                
                // Disable emulated prepared statements
                self::$connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
                // Set the PDO error mode to exception
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (\PDOException $e) {
                throw $e;
            }
        }
        // Return the connection
        return self::$connection;
    }

    /**
     * Check if the database connection is healthy
     * @throws \Exception
     * @return bool
     */
    public static function ping(): bool
    {
        try {
            self::getInstance();
            return true;
        } catch (\Exception $e) {
            if (config('app.throw_unhealthy_services')) {
                if(config('app.debug')) {
                    throw $e;
                }
                throw new \Exception('Database connection is not healthy');
            }
            return false;
        }
    }
    /**
     * Execute a query in the database
     * @param string $query
     * @param array $params
     * @return bool|\PDOStatement
     */
    public static function query(string $query, array $params = []):bool|PDOStatement
    {
        $stmt = self::getInstance()->prepare($query);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch a record from the database
     * @param string $query
     * @param array $params
     * @return bool|array
     */
    public static function fetch(string $query, array $params = []): bool|array
    {
        return self::query($query, $params)
            ->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all records from the database
     * @param string $query
     * @param array $params
     * @return array
     */
    public static function fetchAll(string $query, array $params = []):array
    {
        return self::query($query, $params)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insert a record in the database
     * @param string $query
     * @param array $params
     * @return int
     */
    public static function insert(string $query, array $params = []): int
    {
        $stmt = self::query($query, $params);
        return $stmt->rowCount();
    }

    /**
     * Update a record in the database
     * @param string $query
     * @param array $params
     * @return bool
     */
    public static function update(string $query, array $params = []):bool
    {
        self::query($query, $params);
        return true;
    }

}