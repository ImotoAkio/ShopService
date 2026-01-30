<?php
require_once __DIR__ . '/app/Core/Database.php';
use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    // Drop the incomplete table created by earlier scripts
    echo "Dropping old valve_models table...\n";
    $db->exec("DROP TABLE IF EXISTS valve_models");

    // Run the correct creation script
    echo "Recreating valve_models with correct schema...\n";
    $sql = file_get_contents('update_valve_models.sql');
    $db->exec($sql);

    // Run the insert new models script as well just in case
    echo "Inserting additional models...\n";
    $sql2 = file_get_contents('insert_new_valve_model.sql');
    $db->exec($sql2);

    echo "Valve models fixed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>