USE shopservice;

CREATE TABLE IF NOT EXISTS valve_models (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    service_description TEXT,
    procedures TEXT,
    observations TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Initial Data (Idempotent-ish check not easy in pure SQL without procedure, using simple INSERT IGNORE or assuming fresh table)
TRUNCATE TABLE valve_models; -- Reset for development to ensure clean slate

INSERT INTO valve_models (name, service_description, procedures, observations) VALUES 
('Modelo Padrão (Rosca)', 
'Manutenção de [QTD] VRPs (válvula redutora de pressão) [MODELO]; instaladas em [LOCAL] [TORRE] com a PDS (pressão dinâmica de saída) em [PDS] para atender [ALCANCE].',
'Fechamento do registro de entrada e saída da linha da Válvula Redutora.\n(Não nos responsabilizamos por avarias dos registros...)\n\n1- A ser executado no próprio local.\n2- Remoção do apoio abaixo da VRP...\n3- Verificar o estado das peças internas...\n4- Remover incrustações...\n5- Remontar e testar...',
'A deficiência no funcionamento das VRPs pode ocasionar:\n- Fuga de água.\n- Diminuição de água.\n- Barulho na rede.\n...'),

('Modelo Flangeado (Industrial)', 
'Manutenção Técnica Industrial de [QTD] Válvulas Flangeadas [MODELO]; Local: [LOCAL] - [TORRE]. Pressão Ajustada: [PDS].',
'Procedimento Especial para Flanges:\n1- Bloqueio total da linha.\n2- Desaperto cruzado dos parafusos do flange.\n3- Substituição obrigatória das juntas de vedação.\n4- Inspeção de sedes e discos.\n5- Teste hidrostático de bancada (se necessário).',
'Obs: Válvulas flangeadas requerem maior tempo de cura para vedações líquidas (se utilizadas).');
