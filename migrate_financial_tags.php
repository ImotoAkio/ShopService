<?php
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    $sql = file_get_contents(__DIR__ . '/update_financial_tags.sql');

    // Allow multiple statements
    $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);

    try {
        $db->exec($sql);
        echo "Financial Tags Database updated successfully.<br>";
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
