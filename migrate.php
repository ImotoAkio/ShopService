<?php
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    // Check if column exists
    $stmt = $db->query("SHOW COLUMNS FROM usuarios LIKE 'role'");
    if ($stmt->rowCount() == 0) {
        $db->exec("ALTER TABLE usuarios ADD COLUMN role VARCHAR(20) DEFAULT 'user'");
        echo "Column 'role' added successfully.<br>";
    } else {
        echo "Column 'role' already exists.<br>";
    }

    echo "Database update completed.";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
