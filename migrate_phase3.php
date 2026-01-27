<?php
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    $sql = file_get_contents(__DIR__ . '/update_phase3.sql');
    $db->exec($sql);
    echo "Phase 3 Database updated successfully.<br>";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
