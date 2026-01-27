<?php
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    $sql = file_get_contents(__DIR__ . '/update_assets_schema.sql');

    // Allow multiple statements
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);

    try {
        $db->exec($sql);
        echo "Asset Schema updated successfully.<br>";
    } catch (PDOException $e) {
        // Ignore "Duplicate column name" errors if re-running
        if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
            echo "Columns already exist (Skipped).<br>";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
