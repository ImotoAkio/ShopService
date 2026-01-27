<?php
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "OS Fotos:\n";
$stmt = $db->query("DESCRIBE os_fotos");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\nClient Photos:\n";
$stmt = $db->query("DESCRIBE client_photos");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
