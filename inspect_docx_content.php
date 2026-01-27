<?php
require_once __DIR__ . '/app/Services/ImportService.php';

use App\Services\ImportService;

$service = new ImportService();
$file = __DIR__ . '/storage/orcamentos_word/6976566368654_Agora_Jacana_15676_420.docx';

if (!file_exists($file)) {
    die("File not found: $file\n");
}

$text = $service->extractTextFromDocx($file);
echo "=== TEXT START ===\n";
echo substr($text, 0, 2000); // Show first 2000 chars
echo "\n=== TEXT END ===\n";
