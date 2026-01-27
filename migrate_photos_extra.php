<?php
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

$db = Database::getInstance()->getConnection();

echo "Running migration for Photo Extras...\n";

try {
    // Add fields to os_fotos
    // description (TEXT), observacoes (TEXT)
    // Check if description exists first (it was checked manully and it didn't exist in os_fotos, only in client_photos usually)
    // Actually, client_photos had description. os_fotos did NOT (based on my previous `inspect_db`). 
    // Wait, let's verify `inspect_photos.php` output from history.
    // os_fotos: id, os_id, photo_path, created_at. NO description.
    // client_photos: id, client_id, photo_path, description, created_at.

    $osCols = [
        'description' => 'TEXT',
        'observacoes' => 'TEXT'
    ];

    foreach ($osCols as $col => $type) {
        try {
            $db->exec("ALTER TABLE os_fotos ADD COLUMN $col $type");
            echo "Added $col to os_fotos.\n";
        } catch (Exception $e) {
            echo "Column $col likely exists in os_fotos.\n";
        }
    }

    // Add fields to client_photos
    // observacoes (TEXT)
    try {
        $db->exec("ALTER TABLE client_photos ADD COLUMN observacoes TEXT");
        echo "Added observacoes to client_photos.\n";
    } catch (Exception $e) {
        echo "Column observacoes likely exists in client_photos.\n";
    }

    echo "Migration completed.\n";

} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
