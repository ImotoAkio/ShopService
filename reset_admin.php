<?php
require_once __DIR__ . '/public/index.php'; // Load env, constants
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Database;

try {
    $db = Database::getInstance()->getConnection();

    $email = 'admin@admin.com';
    $password = '123456';
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $name = 'Admin';

    // Check if exists
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        echo "User $email exists. Updating password...\n";
        $stmt = $db->prepare("UPDATE usuarios SET password = ?, name = ?, role = 'admin' WHERE email = ?");
        $stmt->execute([$hash, $name, $email]);
    } else {
        echo "User $email does not exist. Creating...\n";
        $stmt = $db->prepare("INSERT INTO usuarios (name, email, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$name, $email, $hash]);
    }

    echo "DONE. Login with:\n";
    echo "Email: $email\n";
    echo "Pass: $password\n";

} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "\n");
}
