<?php
require_once __DIR__ . '/app/core/Database.php';

use App\Core\Database;

// Basic mocked environment for Database class if needed, or just require the file.
// Assuming 'app/core/Database.php' exists and works.
// We might need to adjust paths if running from root.

echo "Iniciando migração...\n";

try {
    $db = Database::getInstance()->getConnection();

    // Check if columns exist
    $check = $db->query("SHOW COLUMNS FROM ordens_servico LIKE 'validade_meses'");
    if ($check->rowCount() == 0) {
        echo "Adicionando coluna 'validade_meses'...\n";
        $db->exec("ALTER TABLE ordens_servico ADD COLUMN validade_meses INT NULL DEFAULT NULL AFTER relatorio");
    } else {
        echo "Coluna 'validade_meses' já existe.\n";
    }

    $check2 = $db->query("SHOW COLUMNS FROM ordens_servico LIKE 'data_proxima_manutencao'");
    if ($check2->rowCount() == 0) {
        echo "Adicionando coluna 'data_proxima_manutencao'...\n";
        $db->exec("ALTER TABLE ordens_servico ADD COLUMN data_proxima_manutencao DATE NULL DEFAULT NULL AFTER validade_meses");
    } else {
        echo "Coluna 'data_proxima_manutencao' já existe.\n";
    }

    echo "Migração concluída com sucesso!\n";

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
