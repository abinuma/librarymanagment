<?php
/**
 * Setting Model
 * 
 * Handles all database operations related to application settings.
 */

class Setting
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    private static ?array $cache = null;

    /**
     * Load all settings into cache
     */
    private function loadCache(): void
    {
        if (self::$cache === null) {
            self::$cache = [];
            try {
                $stmt = $this->db->query('SELECT setting_key, setting_value FROM settings');
                while ($row = $stmt->fetch()) {
                    self::$cache[$row['setting_key']] = $row['setting_value'];
                }
            } catch (PDOException $e) {
                // Table might not exist yet during installation/migration
            }
        }
    }

    /**
     * Get a setting value by key
     */
    public function get(string $key, $default = null): ?string
    {
        $this->loadCache();
        return self::$cache[$key] ?? $default;
    }

    /**
     * Update a setting value
     */
    public function update(string $key, string $value): bool
    {
        $stmt = $this->db->prepare('UPDATE settings SET setting_value = :val WHERE setting_key = :key');
        $result = $stmt->execute(['val' => $value, 'key' => $key]);
        if ($result) {
            self::$cache = null; // Invalidate cache
        }
        return $result;
    }

    /**
     * Get all settings as key-value pairs
     */
    public function getAll(): array
    {
        $this->loadCache();
        return self::$cache ?? [];
    }
}
