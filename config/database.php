<?php

declare(strict_types=1);

/*
 * =========================================================
 * DATABASE & CORE CONFIGURATION
 * =========================================================
 *
 * Credentials and environment-specific values are loaded
 * from the .env file (never committed to Git) via env.php.
 *
 * Defaults are provided so local development still works,
 * but production MUST define real values in .env.
 * =========================================================
 */

require_once __DIR__ . '/env.php';


/*
 * Application-wide constants.
 *
 * These are read from the environment but exposed as
 * constants because the rest of the application already
 * depends on BASE_URL and SCHOOL_EMAIL_DOMAIN.
 */
define('BASE_URL', env('BASE_URL', '/school-system/public'));
define('SCHOOL_EMAIL_DOMAIN', env('SCHOOL_EMAIL_DOMAIN', '@school.com'));


class Database
{
    private static ?PDO $connection = null;

    private string $host;
    private string $dbName;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->host     = env('DB_HOST', 'localhost');
        $this->dbName   = env('DB_NAME', 'school-system');
        $this->username = env('DB_USERNAME', 'root');
        $this->password = env('DB_PASSWORD', '');
    }

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