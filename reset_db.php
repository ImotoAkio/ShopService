<?php
require 'app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "WARNING: This will delete ALL data except Users.\n";
echo "Starting cleanup...\n";

try {
    $db->exec("SET FOREIGN_KEY_CHECKS = 0");

    // Tables to truncate
    $tables = [
        'ativos',
        'ativo_fotos',
        'clientes',
        'client_photos',
        'financial_entries',
        'financeiro_tags',
        'tags',
        'links',
        'orcamentos',
        'orcamento_itens',
        'ordens_servico',
        'os_fotos',
        'valve_models' // We will re-seed this
    ];

    foreach ($tables as $table) {
        try {
            // Check if table exists first to avoid error if I missed one or it doesn't exist
            $check = $db->query("SHOW TABLES LIKE '$table'");
            if ($check->rowCount() > 0) {
                $db->exec("TRUNCATE TABLE $table");
                echo "Truncated: $table\n";
            }
        } catch (PDOException $e) {
            echo "Error truncating $table: " . $e->getMessage() . "\n";
        }
    }

    $db->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Cleanup complete.\n";

    // Re-seed Valve Models
    echo "Re-seeding Valve Models...\n";
    require_once 'seed_valves.php';

    // Re-seed Tags? (If exists)
    // I don't see a seed_tags.php, but maybe migrate_financial_tags.php does it?
    // Let's check migrate_financial_tags.php to see if it inserts default tags.
    // If so, we should run it or extract logic.

} catch (Exception $e) {
    echo "Critical Error: " . $e->getMessage() . "\n";
}
