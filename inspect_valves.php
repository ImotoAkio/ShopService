<?php
require 'app/Core/Database.php';
use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SELECT id, name, service_description FROM valve_models");
    $models = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($models as $model) {
        echo "ID: " . $model['id'] . "\n";
        echo "Name: " . $model['name'] . "\n";
        echo "Description: \n" . $model['service_description'] . "\n";
        echo "--------------------------------------------------\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
