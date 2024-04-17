<?php

namespace App\Core;

use PDO;

final class Database
{
    private static PDO $connection;

    public static function getConnection(): PDO
    {
        if (!isset(self::$connection)) {
            self::connect();
        }

        return self::$connection;
    }

    protected function connect(): void
    {
        $host = getenv('MYSQL_HOST');
        $port = getenv('MYSQL_PORT');
        $username = getenv('MYSQL_USER');
        $password = getenv('MYSQL_PASSWORD');
        $database = getenv('MYSQL_DATABASE');

        try {
            self::$connection = new PDO(
                "mysql:host=$host; dbname=$database",
                $username,
                $password
            );

            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            exit;
        }
    }
}
