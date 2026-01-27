<?php
require_once __DIR__ . '/vendor/autoload.php';
use App\Core\Database;

// Mock session to avoid constructor redirect if any (though Database usually doesn't need session)
// Actually OrcamentoController handles session. Database class likely doesn't.

// Need to find where Database is.
if (file_exists(__DIR__ . '/app/Core/Database.php')) {
    require_once __DIR__ . '/app/Core/Database.php';
}

try {
    $db = App\Core\Database::getInstance()->getConnection();
    $stmt = $db->query('DESCRIBE clientes');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
