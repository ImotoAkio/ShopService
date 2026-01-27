<?php
require_once __DIR__ . '/../app/Services/ImportService.php';

use App\Services\ImportService;

$service = new ImportService();
$apiKey = $service->getApiKey();

if (!$apiKey) {
    die("API Key not found.\n");
}

$url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

if (isset($data['models'])) {
    echo "Available Models:\n";
    foreach ($data['models'] as $model) {
        echo "- " . $model['name'] . " (Methods: " . implode(', ', $model['supportedGenerationMethods']) . ")\n";
    }
} else {
    echo "Error listing models: " . $response . "\n";
}
