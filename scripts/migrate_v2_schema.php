<?php
require_once __DIR__ . '/../app/Config/Database.php';

use App\Config\Database;

$db = Database::getInstance()->getConnection();

echo "Iniciando migração para Schema v2 (Hierarquia de Orçamentos)...\n";

try {
    // 0. Alter table orcamentos if needed (add columns if missing)
    // We check if columns exist before adding to avoid errors

    // 1. Create orcamento_setores
    $sqlSetores = "CREATE TABLE IF NOT EXISTS orcamento_setores (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        orcamento_id INT NOT NULL,
        nome VARCHAR(100) NOT NULL COMMENT 'Ex: Torre A, Blocos 1 e 2, Área Externa',
        descricao TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_setor_orcamento FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    $db->exec($sqlSetores);
    echo "Tabela 'orcamento_setores' verificada/criada.\n";

    // 2. Create orcamento_zonas
    $sqlZonas = "CREATE TABLE IF NOT EXISTS orcamento_zonas (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        setor_id BIGINT NOT NULL,
        nome VARCHAR(100) NOT NULL COMMENT 'Ex: Zona Baixa, Zona Alta, Barrilete',
        material_tubulacao VARCHAR(50) NOT NULL COMMENT 'PVC Marrom, PPR, Cobre, Galv.',
        faixa_andares VARCHAR(50) COMMENT 'Ex: Térreo ao 10º Andar',
        pressao_trabalho DECIMAL(5,2) COMMENT 'Valor numérico',
        pressao_unidade VARCHAR(10) DEFAULT 'kgf/cm²',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_zona_setor FOREIGN KEY (setor_id) REFERENCES orcamento_setores(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    $db->exec($sqlZonas);
    echo "Tabela 'orcamento_zonas' verificada/criada.\n";

    // 3. Create orcamento_itens
    $sqlItens = "CREATE TABLE IF NOT EXISTS orcamento_itens (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        zona_id BIGINT NOT NULL,
        tipo ENUM('VRP', 'Valvula', 'Filtro', 'Manometro', 'Outros') NOT NULL,
        descricao VARCHAR(255) NOT NULL COMMENT 'Nome comercial, ex: Válvula Redutora Bermad 420',
        marca_modelo VARCHAR(100) COMMENT 'Ex: Bermad 420, Emmetti',
        diametro_bitola VARCHAR(20) COMMENT 'Ex: 2 1/2, 3, 75mm',
        quantidade DECIMAL(10,2) NOT NULL DEFAULT 1,
        valor_unitario_material DECIMAL(10,2) DEFAULT 0.00,
        valor_unitario_mao_obra DECIMAL(10,2) DEFAULT 0.00,
        subtotal DECIMAL(10,2) GENERATED ALWAYS AS (quantidade * (valor_unitario_material + valor_unitario_mao_obra)) STORED,
        CONSTRAINT fk_item_zona FOREIGN KEY (zona_id) REFERENCES orcamento_zonas(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;";
    $db->exec($sqlItens);
    echo "Tabela 'orcamento_itens' verificada/criada.\n";

    echo "Migração concluída com sucesso!\n";

} catch (PDOException $e) {
    die("Erro na migração: " . $e->getMessage() . "\n");
}
