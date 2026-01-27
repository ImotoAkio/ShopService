<?php
// Fix path to Database.php
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

function getTableInfo($db, $table)
{
    echo "Table: $table\n";
    try {
        $stmt = $db->query("PRAGMA table_info($table)");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "- {$row['name']} ({$row['type']})\n";
        }
    } catch (Exception $e) {
        // Fallback for MySQL if PRAGMA fails
        try {
            $stmt = $db->query("DESCRIBE $table");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "- {$row['Field']} ({$row['Type']})\n";
            }
        } catch (Exception $e2) {
            echo " Could not describe table: " . $e2->getMessage();
        }
    }
    echo "\n";
}

try {
    getTableInfo($db, 'clientes');
    getTableInfo($db, 'orcamentos');
    getTableInfo($db, 'orcamento_itens');
    getTableInfo($db, 'ordens_servico');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
