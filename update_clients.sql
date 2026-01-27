USE shopservice;

-- Add document column if it doesn't exist
-- Using a procedure to handle "IF NOT EXISTS" for column addition safely in MySQL/MariaDB
SET @dbname = DATABASE();
SET @tablename = "clientes";
SET @columnname = "documento";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      (table_name = @tablename)
      AND (table_schema = @dbname)
      AND (column_name = @columnname)
  ) > 0,
  "SELECT 1",
  "ALTER TABLE clientes ADD COLUMN documento VARCHAR(20) AFTER name"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;
