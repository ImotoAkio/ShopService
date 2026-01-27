-- Schema for Complex Hydraulic Budgets
-- Supports 4-level hierarchy: Budget -> Groups -> Zones -> Items -> Composition

-- 1. Main Budget Table (Enhanced)
-- Stores the high-level budget info.
CREATE TABLE IF NOT EXISTS orcamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    status VARCHAR(50) DEFAULT 'draft',
    date DATE NOT NULL,
    validity VARCHAR(100) DEFAULT '10 dias',
    warranty VARCHAR(255),
    payment_terms TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    -- Foreign keys would be added here normally
);

-- 2. Budget Groups (Sectors/Buildings)
-- Example: "Blocos 1 e 2", "Blocos 3 e 4"
CREATE TABLE IF NOT EXISTS orcamento_grupos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orcamento_id INT NOT NULL,
    name VARCHAR(255) NOT NULL, -- e.g., "Setor A (Blocos 1 e 2)"
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE CASCADE
);

-- 3. Budget Zones (Prumadas/Pressão)
-- Example: "Zona Baixa", "Zona Alta"
-- Attributes like material and pressure are specific to the ZONE level.
CREATE TABLE IF NOT EXISTS orcamento_zonas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grupo_id INT NOT NULL,
    name VARCHAR(255) NOT NULL, -- e.g., "Zona Baixa"
    
    -- Technical Attributes
    pipeline_material VARCHAR(100), -- e.g., "PVC Marrom", "PPR", "Cobre"
    floor_range VARCHAR(100),       -- e.g., "SS1 ao 4º andar"
    pressure_value DECIMAL(10,2),   -- e.g., 4.0
    pressure_unit VARCHAR(50) DEFAULT 'kgf/cm²', -- e.g., "kgf/cm²", "mca"
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (grupo_id) REFERENCES orcamento_grupos(id) ON DELETE CASCADE
);

-- 4. Budget Items (Equipment/Assets)
-- Example: "VRP", "Filtro Y"
-- These are the main assets being serviced or installed in a specific zone.
CREATE TABLE IF NOT EXISTS orcamento_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    zona_id INT NOT NULL,
    
    -- Asset Details
    name VARCHAR(255) NOT NULL, -- e.g., "Válvula Redutora de Pressão (VRP)"
    quantity INT NOT NULL DEFAULT 1,
    diameter VARCHAR(50),       -- e.g., "2 1/2\"", "3\""
    brand_model VARCHAR(255),   -- e.g., "Bermad 420", "Emmetti"
    
    -- Costing (Optional at this level if granular breakdown is used)
    labor_unit_cost DECIMAL(10,2) DEFAULT 0.00,
    labor_total_cost DECIMAL(10,2) GENERATED ALWAYS AS (quantity * labor_unit_cost) STORED,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (zona_id) REFERENCES orcamento_zonas(id) ON DELETE CASCADE
);

-- 5. Item Composition / Parts (Detailed Financials)
-- Example: "Diafragma", "Mola", "Kit de Reparo"
-- Used for granular material costs per item.
CREATE TABLE IF NOT EXISTS orcamento_itens_composicao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    
    part_name VARCHAR(255) NOT NULL, -- e.g., "Diafragma"
    quantity_per_item INT DEFAULT 1, 
    unit_cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    
    category VARCHAR(50) DEFAULT 'material', -- 'material', 'spare_part', 'labor_extra'
    is_optional BOOLEAN DEFAULT FALSE, -- For "Adicionais se necessário"
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES orcamento_itens(id) ON DELETE CASCADE
);
