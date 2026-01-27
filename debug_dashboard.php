<?php
// Debug Script
define('BASE_URL', 'http://localhost/shopservice');
require 'app/Core/Database.php';

try {
    echo "Connecting to DB...\n";
    $db = \App\Core\Database::getInstance()->getConnection();
    echo "Connected.\n";

    $where = "WHERE 1=1";
    $params = [];

    $sqlList = "
        SELECT f.*, 
               GROUP_CONCAT(CONCAT(t.name, '|', t.color) SEPARATOR ',') as tags 
        FROM financeiro f
        LEFT JOIN financeiro_tags ft ON f.id = ft.financeiro_id
        LEFT JOIN tags t ON ft.tag_id = t.id
        $where
        GROUP BY f.id 
        ORDER BY f.data_vencimento DESC 
        LIMIT 10
    ";

    echo "Preparing SQL...\n";
    $stmt = $db->prepare($sqlList);
    echo "Executing SQL...\n";
    $stmt->execute($params);
    echo "Fetching...\n";
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Count: " . count($results) . "\n";
    if (count($results) > 0) {
        print_r($results[0]);
    } else {
        echo "No results.\n";
    }

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
