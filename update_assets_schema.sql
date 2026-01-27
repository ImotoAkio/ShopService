USE shopservice;

-- Link Ativos to Clientes
ALTER TABLE ativos ADD COLUMN client_id INT NULL;
ALTER TABLE ativos ADD CONSTRAINT fk_ativo_client FOREIGN KEY (client_id) REFERENCES clientes(id) ON DELETE SET NULL;

-- Link OS to Ativos
ALTER TABLE ordens_servico ADD COLUMN ativo_id INT NULL;
ALTER TABLE ordens_servico ADD CONSTRAINT fk_os_ativo FOREIGN KEY (ativo_id) REFERENCES ativos(id) ON DELETE SET NULL;

-- Link Orcamentos to Ativos
ALTER TABLE orcamentos ADD COLUMN ativo_id INT NULL;
ALTER TABLE orcamentos ADD CONSTRAINT fk_orcamento_ativo FOREIGN KEY (ativo_id) REFERENCES ativos(id) ON DELETE SET NULL;
