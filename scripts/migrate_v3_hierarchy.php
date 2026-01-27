<?php
require_once __DIR__ . '/../app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "Iniciando migração para Schema v3 (Hierarquia Completa)...\n";

try {
    // 1. Create orcamento_setores
    $sqlSetores = "CREATE TABLE IF NOT EXISTS orcamento_setores (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        orcamento_id INT NOT NULL,
        name VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_setor_orcamento FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    $db->exec($sqlSetores);
    echo "Tabela 'orcamento_setores' verificada/criada.\n";

    // 2. Create orcamento_zonas
    $sqlZonas = "CREATE TABLE IF NOT EXISTS orcamento_zonas (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        setor_id BIGINT NOT NULL,
        name VARCHAR(255) NOT NULL,
        pipeline_material VARCHAR(100) NOT NULL COMMENT 'Material da Tubulação (Ex: PVC, PPR)',
        pressure_value DECIMAL(10,2),
        pressure_unit VARCHAR(20) DEFAULT 'kgf/cm²',
        floor_range VARCHAR(100),
        location VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_zona_setor FOREIGN KEY (setor_id) REFERENCES orcamento_setores(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    $db->exec($sqlZonas);
    echo "Tabela 'orcamento_zonas' verificada/criada.\n";

    // 3. Update orcamento_itens
    // Check if columns exist before adding
    $checkCols = $db->query("SHOW COLUMNS FROM orcamento_itens");
    $columns = $checkCols->fetchAll(PDO::FETCH_COLUMN);

    if (!in_array('zona_id', $columns)) {
        $db->exec("ALTER TABLE orcamento_itens ADD COLUMN zona_id BIGINT NULL");
        $db->exec("ALTER TABLE orcamento_itens ADD CONSTRAINT fk_item_zona FOREIGN KEY (zona_id) REFERENCES orcamento_zonas(id) ON DELETE CASCADE");
        echo "Coluna 'zona_id' adicionada em 'orcamento_itens'.\n";
    }

    if (!in_array('brand_model', $columns)) {
        $db->exec("ALTER TABLE orcamento_itens ADD COLUMN brand_model VARCHAR(255)");
        echo "Coluna 'brand_model' adicionada em 'orcamento_itens'.\n";
    }

    if (!in_array('diameter', $columns)) {
        $db->exec("ALTER TABLE orcamento_itens ADD COLUMN diameter VARCHAR(50)");
        echo "Coluna 'diameter' adicionada em 'orcamento_itens'.\n";
    }

    // Ensure English/Portuguese Compat if 'descricao' vs 'description' exists?
    // Current Controller uses 'description' in INSERT: `INSERT INTO orcamento_itens (orcamento_id, description...`
    // Wait, let's check `migrate_v2_schema.php` again. It used "descricao".
    // "create table ... descricao VARCHAR(255)..."
    // BUT Controller uses `description`.
    // Let's verify what columns ACTUALLY exist by check.
    // I will add both if missing or alias them. 
    // Wait, line 365 of Controller: `INSERT INTO orcamento_itens (..., description, ...)`
    // So 'description' MUST exist currently?
    // Let's check `migrate_v2_schema.php` creates table with `descricao`. 
    // Controller might be broken or I misread? 
    // Ah, previous file view of `migrate_v2_schema.php` (lines 42-54): `descricao VARCHAR(255)`.
    // Controller (lines 365): `INSERT INTO ... (..., description, ...)`
    // One of them is wrong. If schema v2 was run, Controller is broken.
    // I'll stick to 'description' as requested by User ("description" field in prompt) and check via ADD COLUMN if needed.

    if (!in_array('description', $columns) && in_array('descricao', $columns)) {
        // Either rename or just use description. Let's add description for safety if mostly used.
        // Actually, I'll rely on the user instructions which prefer English keys?
        // User requested: "Campos: quantity, description, diameter, unit_price, total_price"
        $db->exec("ALTER TABLE orcamento_itens CHANGE COLUMN descricao description VARCHAR(255) NULL");
        echo "Coluna 'descricao' renomeada para 'description'.\n";
    } elseif (!in_array('description', $columns)) {
        $db->exec("ALTER TABLE orcamento_itens ADD COLUMN description VARCHAR(255)");
    }

    echo "Migração concluída com sucesso!\n";

} catch (PDOException $e) {
    die("Erro na migração: " . $e->getMessage() . "\n");
}
