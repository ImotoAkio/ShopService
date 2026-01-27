USE shopservice;

ALTER TABLE ordens_servico ADD COLUMN orcamento_id INT NULL DEFAULT NULL;
ALTER TABLE ordens_servico ADD CONSTRAINT fk_os_orcamento FOREIGN KEY (orcamento_id) REFERENCES orcamentos(id) ON DELETE SET NULL;

ALTER TABLE orcamentos ADD COLUMN os_id INT NULL DEFAULT NULL;
ALTER TABLE orcamentos ADD CONSTRAINT fk_orcamento_os FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE SET NULL;
