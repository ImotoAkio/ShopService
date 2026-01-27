<?php
require_once 'app/Core/Database.php';

try {
    $db = App\Core\Database::getInstance()->getConnection();

    // Check if exists
    $check = $db->query("SHOW COLUMNS FROM ordens_servico LIKE 'tipo'");
    if ($check->rowCount() == 0) {
        $sql = "ALTER TABLE ordens_servico ADD COLUMN tipo VARCHAR(50) DEFAULT 'Execução' AFTER status";
        $db->exec($sql);
        echo "Added 'tipo' column to ordens_servico.\n";
    } else {
        echo "'tipo' column already exists.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
