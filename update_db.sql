USE shopservice;

-- Add role column if it doesn't exist (running this multiple times on some SQL versions might error, but 'admin' insertion handles duplicates)
ALTER TABLE usuarios ADD COLUMN role VARCHAR(20) DEFAULT 'user';

-- Update existing admin user to have 'admin' role
UPDATE usuarios SET role = 'admin' WHERE email = 'admin@admin.com';
