<?php

define('BASE_URL', '/school-system/public');
define('SCHOOL_EMAIL_DOMAIN', '@school.com');
class Database
{
    private static ?PDO $connection = null;

    private string $host = 'localhost';
    private string $dbName = 'school-system';
    private string $username = 'root';
    private string $password = '';

    public static function getConnection(): PDO
    {
        if (self::$connection === null) {
            $db = new self();
            self::$connection = $db->connect();
        }

        return self::$connection;
    }

    private function connect(): PDO
    {
        $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";

        try {
            $pdo = new PDO($dsn, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
}