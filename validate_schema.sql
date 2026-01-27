-- Validation Script for "New Way" Use Case
-- This script simulates the insertion of the complex budget described in the requirements.

-- 1. Create a Dummy Budget
INSERT INTO orcamentos (id, client_id, date, validity) 
VALUES (1001, 1, '2023-10-27', '15 dias');

-- 2. Insert Sectors (Groups)
-- Setor A (Blocos 1 e 2)
INSERT INTO orcamento_grupos (id, orcamento_id, name) VALUES (1, 1001, 'Setor A (Blocos 1 e 2)');
-- Setor B (Blocos 3 e 4)
INSERT INTO orcamento_grupos (id, orcamento_id, name) VALUES (2, 1001, 'Setor B (Blocos 3 e 4)');


-- 3. Insert Zones for Setor A
-- Zona Baixa A: PVC Marrom, 4kgf
INSERT INTO orcamento_zonas (id, grupo_id, name, pipeline_material, pressure_value, pressure_unit) 
VALUES (10, 1, 'Zona Baixa', 'PVC Marrom', 4.0, 'kgf/cm²');

-- Zona Alta A: PPR, 5.5kgf
INSERT INTO orcamento_zonas (id, grupo_id, name, pipeline_material, pressure_value, pressure_unit) 
VALUES (11, 1, 'Zona Alta', 'PPR', 5.5, 'kgf/cm²');


-- 4. Insert Zones for Setor B
-- Zona Baixa B: PVC Marrom, 4kgf (Same specs, different object)
INSERT INTO orcamento_zonas (id, grupo_id, name, pipeline_material, pressure_value, pressure_unit) 
VALUES (20, 2, 'Zona Baixa', 'PVC Marrom', 4.0, 'kgf/cm²');

-- Zona Alta B: PPR, 5.5kgf, Different floor range
INSERT INTO orcamento_zonas (id, grupo_id, name, pipeline_material, floor_range, pressure_value, pressure_unit) 
VALUES (21, 2, 'Zona Alta', 'PPR', '5º ao 18º Andar', 5.5, 'kgf/cm²');


-- 5. Insert Items (Equipment)
-- Setor A -> Zona Baixa -> 2 VRPs
INSERT INTO orcamento_itens (zona_id, name, quantity, diameter, brand_model) 
VALUES (10, 'Válvula Redutora de Pressão (VRP)', 2, '3"', 'Bermad 420');

-- Setor A -> Zona Alta -> 2 VRPs
INSERT INTO orcamento_itens (zona_id, name, quantity, diameter, brand_model) 
VALUES (11, 'Válvula Redutora de Pressão (VRP)', 2, '2 1/2"', 'Bermad 405');

-- Setor B -> Zona Baixa -> 2 VRPs
INSERT INTO orcamento_itens (zona_id, name, quantity, diameter, brand_model) 
VALUES (20, 'VRP', 2, '3"', 'Bermad 420');

-- Setor B -> Zona Alta -> 2 VRPs (Different context)
INSERT INTO orcamento_itens (zona_id, name, quantity, diameter, brand_model) 
VALUES (21, 'VRP', 2, '2 1/2"', 'Emmetti');


-- 6. Verification Query
-- This query reconstructs the full hierarchy to prove the data is structured correctly.
SELECT 
    b.id AS Budget_ID,
    g.name AS Sector,
    z.name AS Zone,
    z.pipeline_material AS Material,
    z.floor_range AS Floors,
    CONCAT(z.pressure_value, ' ', z.pressure_unit) AS Pressure,
    i.quantity AS Qty,
    i.name AS Item,
    i.brand_model AS Model
FROM orcamentos b
JOIN orcamento_grupos g ON g.orcamento_id = b.id
JOIN orcamento_zonas z ON z.grupo_id = g.id
JOIN orcamento_itens i ON i.zona_id = z.id
WHERE b.id = 1001
ORDER BY g.name, z.name;
