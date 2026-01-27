USE shopservice;

-- Update Clientes Table
ALTER TABLE clientes ADD COLUMN cnpj VARCHAR(20) AFTER name;
ALTER TABLE clientes ADD COLUMN contact_name VARCHAR(100) AFTER address;
ALTER TABLE clientes ADD COLUMN contact_role VARCHAR(100) AFTER contact_name;
ALTER TABLE clientes ADD COLUMN phone2 VARCHAR(20) AFTER phone;

-- Update Orcamentos Table
ALTER TABLE orcamentos ADD COLUMN subject VARCHAR(255) AFTER status;
ALTER TABLE orcamentos ADD COLUMN service_description TEXT AFTER subject;
ALTER TABLE orcamentos ADD COLUMN procedures TEXT AFTER service_description;
ALTER TABLE orcamentos ADD COLUMN observations TEXT AFTER procedures;
ALTER TABLE orcamentos ADD COLUMN duration VARCHAR(100) AFTER observations;
ALTER TABLE orcamentos ADD COLUMN validity VARCHAR(100) DEFAULT '10 dias' AFTER duration;
ALTER TABLE orcamentos ADD COLUMN payment_terms TEXT AFTER validity;
