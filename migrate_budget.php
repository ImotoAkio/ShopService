<?php
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "Running migration for Budget Customization...\n";

try {
    // Add fields to clientes
    $columns = [
        'responsavel' => 'VARCHAR(100)',
        'cargo' => 'VARCHAR(100)',
        'telefone2' => 'VARCHAR(20)',
        'cnpj' => 'VARCHAR(20)'
    ];

    foreach ($columns as $col => $type) {
        try {
            $db->exec("ALTER TABLE clientes ADD COLUMN $col $type");
            echo "Added $col to clientes.\n";
        } catch (Exception $e) {
            echo "Column $col likely exists in clientes (or error: " . $e->getMessage() . ")\n";
        }
    }

    // Add fields to orcamentos
    $orcColumns = [
        'assunto' => 'VARCHAR(255)',
        'servico_descricao' => 'TEXT',
        'procedimentos' => 'TEXT',
        'duracao' => 'VARCHAR(50)',
        'garantia' => 'VARCHAR(100)',
        'forma_pagamento' => 'VARCHAR(100)',
        'observacoes' => 'TEXT',
        'validade' => 'VARCHAR(50)'
    ];

    foreach ($orcColumns as $col => $type) {
        try {
            $db->exec("ALTER TABLE orcamentos ADD COLUMN $col $type");
            echo "Added $col to orcamentos.\n";
        } catch (Exception $e) {
            echo "Column $col likely exists in orcamentos (or error: " . $e->getMessage() . ")\n";
        }
    }

    echo "Migration completed.\n";

} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
