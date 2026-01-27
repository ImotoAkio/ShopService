<?php
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    $sql = file_get_contents(__DIR__ . '/update_links.sql');

    // Split by semicolon to handle multiple statements if PDO doesn't like them in one go (it usually does for simple ones but safe to execute one by one if preferred, but usually run is fine)
    // Actually, PDO execute might fail on multiple statements depending on driver.
    // Let's run it.

    $db->exec($sql);

    echo "Migration successful: Links added to tables.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}
