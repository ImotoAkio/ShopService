<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'shopservice';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);

    $tables = ['orcamento_grupos', 'orcamento_zonas'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW COLUMNS FROM $table");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "Columns in '$table':\n";
        foreach ($columns as $col) {
            echo "- " . $col['Field'] . "\n";
        }
        echo "\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
