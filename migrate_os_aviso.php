<?php
require 'app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "Checking 'ordens_servico' table...\n";

try {
    // Check if column exists
    $stmt = $db->query("SHOW COLUMNS FROM ordens_servico LIKE 'aviso'");
    $column = $stmt->fetch();

    if (!$column) {
        echo "Adding 'aviso' column...\n";
        $db->exec("ALTER TABLE ordens_servico ADD COLUMN aviso TEXT AFTER relatorio");
        echo "Column 'aviso' added successfully.\n";
    } else {
        echo "Column 'aviso' already exists.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
