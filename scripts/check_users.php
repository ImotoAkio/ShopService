<?php
require_once __DIR__ . '/../app/Core/Database.php';
use App\Core\Database;

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, name, email FROM usuarios");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
