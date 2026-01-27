<?php
require_once __DIR__ . '/app/Services/ImportService.php';

use App\Services\ImportService;

$service = new ImportService();
$key = $service->getApiKey();

echo "Debug API Key Selection:\n";
if ($key) {
    echo "Key found: " . substr($key, 0, 10) . "...\n";
    if (strpos($key, 'sk-') === 0) {
        echo "Provider Detected: OpenAI\n";
    } elseif (strpos($key, 'AIza') === 0) {
        echo "Provider Detected: Gemini\n";
    } else {
        echo "Provider Detected: Unknown\n";
    }
} else {
    echo "No API Key found.\n";
}

// Check .env content manually to be sure
echo "\nChecking .env file directly:\n";
$envPath = __DIR__ . '/.env';
if (file_exists($envPath)) {
    echo file_get_contents($envPath);
} else {
    echo ".env file not found at $envPath\n";
}
