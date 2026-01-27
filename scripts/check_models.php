<?php
require __DIR__ . '/../app/Core/Database.php';
require __DIR__ . '/../config/config.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, name FROM valve_models WHERE name LIKE '%Emetti%'");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

print_r($rows);
