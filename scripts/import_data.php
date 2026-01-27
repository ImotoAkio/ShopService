<?php
/**
 * Script de Importação de Dados (Clientes e Orçamentos) via JSON
 * Uso: php scripts/import_data.php [caminho_para_arquivo.json]
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

// Check args
if ($argc < 2) {
    echo "Uso: php scripts/import_data.php [arquivo.json]\n";
    echo "Exemplo: php scripts/import_data.php dados.json\n";
    exit(1);
}

$file = $argv[1];

if (!file_exists($file)) {
    die("Erro: Arquivo '$file' não encontrado.\n");
}

$json = file_get_contents($file);
$data = json_decode($json, true);

if (!$data) {
    die("Erro: Falha ao decodificar JSON. Verifique a sintaxe.\n");
}

$db = Database::getInstance()->getConnection();

echo "Iniciando importação de " . count($data) . " registros...\n";

try {
    foreach ($data as $entry) {
        $db->beginTransaction();

        // 1. Process Client
        if (!isset($entry['cliente']) || empty($entry['cliente']['name'])) {
            echo "Aviso: Registro 'cliente' inválido ou sem nome. Pulando...\n";
            $db->rollBack();
            continue;
        }

        $c = $entry['cliente'];
        $clientId = null;

        // Check if exists (by CNPJ or Documento or Name as fallback)
        $stmtCheck = $db->prepare("SELECT id FROM clientes WHERE (cnpj IS NOT NULL AND cnpj != '' AND cnpj = ?) OR (documento IS NOT NULL AND documento != '' AND documento = ?) OR name = ?");
        $cnpj = $c['cnpj'] ?? '';
        $doc = $c['documento'] ?? '';
        $name = $c['name'];

        $stmtCheck->execute([$cnpj, $doc, $name]);
        $existing = $stmtCheck->fetch();

        if ($existing) {
            $clientId = $existing['id'];
            echo " > Cliente existente: $name (ID: $clientId)\n";
            // Optional: Update client data? For now, skip update, just link.
        } else {
            // Insert Client
            echo " > Criando cliente: $name...\n";
            $sqlClient = "INSERT INTO clientes (
                name, documento, cnpj, email, phone, address, responsavel, cargo, telefone2, 
                zelador_nome, zelador_tel, zelador_tel2, zelador_email,
                sindico_nome, sindico_tel, sindico_tel2, sindico_email,
                admin_nome, admin_tel, admin_tel2, admin_email,
                created_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

            $stmtInsert = $db->prepare($sqlClient);
            $stmtInsert->execute([
                $c['name'],
                $c['documento'] ?? '',
                $c['cnpj'] ?? null,
                $c['email'] ?? '',
                $c['phone'] ?? '',
                $c['address'] ?? '',
                $c['responsavel'] ?? '',
                $c['cargo'] ?? '',
                $c['telefone2'] ?? '',
                $c['zelador_nome'] ?? '',
                $c['zelador_tel'] ?? '',
                $c['zelador_tel2'] ?? '',
                $c['zelador_email'] ?? '',
                $c['sindico_nome'] ?? '',
                $c['sindico_tel'] ?? '',
                $c['sindico_tel2'] ?? '',
                $c['sindico_email'] ?? '',
                $c['admin_nome'] ?? '',
                $c['admin_tel'] ?? '',
                $c['admin_tel2'] ?? '',
                $c['admin_email'] ?? ''
            ]);
            $clientId = $db->lastInsertId();
        }

        // 2. Process Budgets (Orcamentos)
        if (isset($entry['orcamentos']) && is_array($entry['orcamentos'])) {
            foreach ($entry['orcamentos'] as $orc) {
                echo "   >> Criando orçamento: " . ($orc['assunto'] ?? 'Sem Assunto') . "...\n";

                // Defaults
                $userId = 1; // Default admin user or specified?
                $status = $orc['status'] ?? 'Pendente';
                $total = $orc['total'] ?? 0;
                $createdAt = $orc['created_at'] ?? date('Y-m-d H:i:s');

                $sqlOrc = "INSERT INTO orcamentos (
                    client_id, user_id, status, total, servico_descricao, 
                    assunto, garantia, validade, forma_pagamento, observacoes, procedimentos, 
                    created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $stmtOrc = $db->prepare($sqlOrc);
                $stmtOrc->execute([
                    $clientId,
                    $userId,
                    $status,
                    $total,
                    $orc['servico_descricao'] ?? '',
                    $orc['assunto'] ?? '',
                    $orc['garantia'] ?? '',
                    $orc['validade'] ?? '',
                    $orc['forma_pagamento'] ?? '',
                    $orc['observacoes'] ?? '',
                    $orc['procedimentos'] ?? '',
                    $createdAt
                ]);

                $orcId = $db->lastInsertId();

                // 3. Process Items
                if (isset($orc['itens']) && is_array($orc['itens'])) {
                    $sqlItem = "INSERT INTO orcamento_itens (orcamento_id, description, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)";
                    $stmtItem = $db->prepare($sqlItem);

                    foreach ($orc['itens'] as $item) {
                        $desc = $item['description'] ?? 'Item';
                        $qtd = $item['quantity'] ?? 1;
                        $unit = $item['unit_price'] ?? 0;
                        $totalItem = $item['total_price'] ?? ($qtd * $unit);

                        $stmtItem->execute([$orcId, $desc, $qtd, $unit, $totalItem]);
                    }
                }
            }
        }

        $db->commit();
    }

    echo "\nImportação concluída com sucesso!\n";

} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "Erro Fatal: " . $e->getMessage() . "\n";
}
