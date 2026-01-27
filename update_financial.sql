USE shopservice;

CREATE TABLE IF NOT EXISTS financeiro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    tipo ENUM('receita', 'despesa') NOT NULL,
    data_vencimento DATE NOT NULL,
    status ENUM('pago', 'pendente') DEFAULT 'pendente',
    os_id_vinculo INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id_vinculo) REFERENCES ordens_servico(id) ON DELETE SET NULL
);

-- Check if column exists before adding (MariaDB/MySQL doesn't support IF NOT EXISTS for columns easily in one line without procedure, 
-- but for simplicity in this dev env, we can just run the ALTER and ignore error, or use a block).
-- We will just try to add it. If it fails, it might be because it exists.
-- Adding payment_status to ordens_servico
ALTER TABLE ordens_servico ADD COLUMN payment_status ENUM('Pendente', 'Pago') DEFAULT 'Pendente';
