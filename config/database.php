<?php
// ==========================================
// database.php
// ==========================================
/**
 * Database Configuration
 * 
 * Centralized database connection using PDO with secure defaults.
 * Uses singleton pattern to prevent multiple connections.
 */

class Database
{
    private static ?PDO $instance = null;

    // Database credentials
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'library_management';
    private const DB_USER = 'root';
    private const DB_PASS = '';
    private const DB_PORT = 3306;
    private const DB_CHARSET = 'utf8mb4';

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct() {}

    /**
     * Automatically create database and tables if they don't exist
     */
    private static function setupDatabase(): void
    {
        // Connection without dbname
        $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', self::DB_HOST, self::DB_PORT, self::DB_CHARSET);
        
        try {
            $pdo = new PDO($dsn, self::DB_USER, self::DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            
            // 1. Create Database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS " . self::DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE " . self::DB_NAME);
            
            // 2. Check if tables exist (using 'users' as a sentinel)
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            if ($stmt->rowCount() === 0) {
                $sqlPath = ROOT_PATH . '/sql/library_management.sql';
                if (file_exists($sqlPath)) {
                    $sql = file_get_contents($sqlPath);
                    // FIX: Split SQL statements properly to handle multiple statements
                    $statements = array_filter(array_map('trim', explode(';', $sql)));
                    foreach ($statements as $statement) {
                        if (!empty($statement)) {
                            $pdo->exec($statement);
                        }
                    }
                }
            }

            // 3. Run missing migrations (e.g. settings table)
            $stmt = $pdo->query("SHOW TABLES LIKE 'settings'");
            if ($stmt->rowCount() === 0) {
                $migrationPath = ROOT_PATH . '/sql/migration_settings.sql';
                if (file_exists($migrationPath)) {
                    $sql = file_get_contents($migrationPath);
                    // FIX: Split SQL statements properly
                    $statements = array_filter(array_map('trim', explode(';', $sql)));
                    foreach ($statements as $statement) {
                        if (!empty($statement)) {
                            $pdo->exec($statement);
                        }
                    }
                }
            }

            // 4. Run transactions migration if borrow_transaction_items doesn't exist
            $stmt = $pdo->query("SHOW TABLES LIKE 'borrow_transaction_items'");
            if ($stmt->rowCount() === 0) {
                $migrationPath = ROOT_PATH . '/sql/migration_transactions.sql';
                if (file_exists($migrationPath)) {
                    $sql = file_get_contents($migrationPath);
                    // FIX: Split SQL statements properly
                    $statements = array_filter(array_map('trim', explode(';', $sql)));
                    foreach ($statements as $statement) {
                        if (!empty($statement)) {
                            $pdo->exec($statement);
                        }
                    }
                }
            }
        } catch (PDOException $e) {
            // If we can't even connect to MySQL, the main getInstance will handle the error
            error_log('Database Setup Error: ' . $e->getMessage());
        }
    }

    /**
     * Get PDO database connection instance (Singleton)
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            // Ensure DB and Tables exist
            self::setupDatabase();

            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                self::DB_HOST,
                self::DB_PORT,
                self::DB_NAME,
                self::DB_CHARSET
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
            ];

            try {
                self::$instance = new PDO($dsn, self::DB_USER, self::DB_PASS, $options);
            } catch (PDOException $e) {
                throw new DatabaseException("Unable to connect to the database system. Please verify server availability.", 500, $e);
            }
        }

        return self::$instance;
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}
}