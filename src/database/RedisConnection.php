<?php
namespace src\database;

use Predis\Client;

/**
 * Class RedisConnection - A class to manage the Redis connection
 * Singleton pattern
 * @package src\database
 * @version 1.0
 * @since 1.0
 * @see \Predis\Client
 */
class RedisConnection
{
    /**
     * The Redis connection
     * @var \Predis\Client
     */
    private static Client $redis;

    public static function connect(): bool|Client
    {
        // If the connection is not set, create it
        if (!isset(self::$redis)) {
            try {
                // Get the Redis configuration
                self::$redis = new Client([
                    'scheme' => 'tcp',
                    'host' => config('redis.host'),
                    'port' => config('redis.port'),
                    'password' => config('redis.password'),
                ], ['cluster' => 'uploads-service']);
            } catch (\Exception $e) {
                throw $e;
            }
        }
        return self::$redis;
    }

    /**
     * Check if the Redis connection is healthy
     * @throws \Exception
     * @return bool
     */
    public static function ping():bool
    {
        try {
            // Check if the connection is healthy
            if($client = self::connect()) {
                // Ping the Redis server
                return (bool) $client->ping();
            }
            // Return false if the connection is not healthy
            return false;
        } catch (\Exception $e) {
            // If the app is configured to throw unhealthy services, throw the exception
            if (config('app.throw_unhealthy_services')) {
                // If the app is in debug mode, throw the exception
                if (config('app.debug')) {
                    throw $e;
                }
                throw new \Exception('Database connection is not healthy');
            }
            return false;
        }
    }

    /**
     * Get a value from Redis
     * @param string $key
     * @return array|null
     */
    public static function get(string $key): array|null
    {
        // Get the value from Redis
        $value = self::connect()->get($key);
        // If the value is null, return null otherwise decode the value
        return $value === null ? $value : json_decode($value, true);
    }

    /**
     * Set a value in Redis
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public static function set(string $key, mixed $value): bool
    {
        // Set the value in Redis
        self::connect()->set($key, json_encode($value));
        // Set the expiration time to 24 hours
        self::connect()->expire($key, 86400);
        return true;
    }
}