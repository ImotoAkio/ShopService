<?php
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    $sql = file_get_contents(__DIR__ . '/update_phase2.sql');

    // Execute multiple queries (simple split, assuming no complex triggers/procedures with semicolons inside strings)
    // PDO::exec usually supports multiple queries in one call depending on driver, but let's be safe if not.
    // Actually MySQL PDO often supports it if emulation is on.
    // Let's split by semicolon just in case, or try running whole block.

    $db->exec($sql);
    echo "Phase 2 Database updated successfully.<br>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
