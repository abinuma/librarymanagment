<?php
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

$db = Database::getInstance();

$sql = file_get_contents(__DIR__ . '/sql/migration_settings.sql');

try {
    $db->exec($sql);
    echo "Migration completed successfully!\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
