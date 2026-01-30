<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'shopservice';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $stmt = $pdo->query("SHOW COLUMNS FROM valve_models");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Columns in 'valve_models':\n";
    foreach ($columns as $col) {
        echo "- " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
