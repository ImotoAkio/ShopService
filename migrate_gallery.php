<?php
require 'app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "Checking 'client_photos' table...\n";

try {
    $sql = "CREATE TABLE IF NOT EXISTS client_photos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        client_id INT NOT NULL,
        photo_path VARCHAR(255) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (client_id) REFERENCES clientes(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

    $db->exec($sql);
    echo "Table 'client_photos' created or already exists.\n";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
