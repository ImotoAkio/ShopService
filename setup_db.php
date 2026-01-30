<?php
$host = 'localhost';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Creating database if not exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS shopservice");
    $pdo->exec("USE shopservice");

    $files = [
        'database.sql',
        'update_phase2.sql',
        'schema_v2.sql',
        'update_financial.sql',
        'update_db.sql',
        'update_clients.sql',
        'update_assets_schema.sql',
        'update_budget_model.sql',
        'update_links.sql',
        'update_phase3.sql',
        'update_financial_tags.sql',
        'update_tags_color.sql',
        'update_valve_models.sql',
        'insert_new_valve_model.sql'
    ];

    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, 1);

    foreach ($files as $file) {
        if (file_exists($file)) {
            echo "Running $file...\n";
            $sql = file_get_contents($file);
            try {
                $pdo->exec($sql);
            } catch (Exception $e) {
                echo "Error running $file: " . $e->getMessage() . "\n";
            }
        } else {
            echo "File $file not found - skipping.\n";
        }
    }
    
    echo "Database setup completed.\n";

} catch (PDOException $e) {
    die("DB Error: " . $e->getMessage());
}
?>
