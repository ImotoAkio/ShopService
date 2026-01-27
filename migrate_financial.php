<?php
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    // Split SQL because ALTER TABLE might fail if column exists, blocking the rest if run as single block?
    // Actually, let's run them.
    $sql = file_get_contents(__DIR__ . '/update_financial.sql');

    // Allow multiple statements
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);

    try {
        $db->exec($sql);
        echo "Financial Database updated successfully.<br>";
    } catch (PDOException $e) {
        // If error contains "Duplicate column", we can ignore it for the ALTER statement
        if (strpos($e->getMessage(), "Duplicate column") !== false) {
            echo "Column already exists, proceeding.<br>";
        } else {
            throw $e;
        }
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
