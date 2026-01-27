<?php
require_once 'app/Core/Database.php';

try {
    $db = App\Core\Database::getInstance()->getConnection();

    $fields = [
        'zelador_nome' => 'VARCHAR(100)',
        'zelador_tel' => 'VARCHAR(20)',
        'zelador_tel2' => 'VARCHAR(20)',
        'zelador_email' => 'VARCHAR(100)',

        'sindico_nome' => 'VARCHAR(100)',
        'sindico_tel' => 'VARCHAR(20)',
        'sindico_tel2' => 'VARCHAR(20)',
        'sindico_email' => 'VARCHAR(100)',

        'admin_nome' => 'VARCHAR(100)',
        'admin_tel' => 'VARCHAR(20)',
        'admin_tel2' => 'VARCHAR(20)',
        'admin_email' => 'VARCHAR(100)'
    ];

    foreach ($fields as $field => $type) {
        // Check if exists
        $check = $db->query("SHOW COLUMNS FROM clientes LIKE '$field'");
        if ($check->rowCount() == 0) {
            $sql = "ALTER TABLE clientes ADD COLUMN $field $type";
            $db->exec($sql);
            echo "Added $field\n";
        } else {
            echo "Skipped $field (exists)\n";
        }
    }

    echo "Migration completed successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
