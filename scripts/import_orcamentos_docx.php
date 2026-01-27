<?php
/**
 * Script de Importação de Orçamentos via .docx (Lote)
 * Usa Gemini 1.5 Flash para estruturar dados do texto extraído.
 *
 * Uso: php scripts/import_orcamentos_docx.php
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Core/Database.php';

// Simple Autoloader for Services/Controllers if Composer is not fully mapped for them
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0)
        return;
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file))
        require $file;
});

use App\Core\Database;
use App\Services\ImportService;

// --- Configuração ---
$STORAGE_DIR = __DIR__ . '/../storage/orcamentos_word';
$SUCCESS_DIR = $STORAGE_DIR . '/processados/sucesso';
$ERROR_DIR = $STORAGE_DIR . '/processados/erro';

// Certifica pastas
if (!is_dir($SUCCESS_DIR))
    mkdir($SUCCESS_DIR, 0777, true);
if (!is_dir($ERROR_DIR))
    mkdir($ERROR_DIR, 0777, true);

// --- Instancia Serviço ---
$importService = new ImportService();
$apiKey = $importService->getApiKey();

if (!$apiKey) {
    die("Erro: API KEY não configurada no arquivo .env ou variáveis de ambiente.\n");
}

// --- Main Loop ---

echo "Iniciando processamento de orçamentos em: $STORAGE_DIR\n";

$files = glob($STORAGE_DIR . '/*.docx');

if (empty($files)) {
    echo "Nenhum arquivo .docx encontrado.\n";
    exit(0);
}

$db = Database::getInstance()->getConnection();

foreach ($files as $file) {
    echo "\nProcessando: " . basename($file) . "...\n";

    // 1. Extração
    $text = $importService->extractTextFromDocx($file);
    if (!$text) {
        echo " [x] Erro: Falha ao extrair texto ou arquivo vazio/inválido.\n";
        rename($file, $ERROR_DIR . '/' . basename($file));
        continue;
    }

    if (strlen($text) < 50) {
        echo " [x] Aviso: Texto muito curto (" . strlen($text) . " chars). Pode ser arquivo corrompido ou imagem.\n";
        rename($file, $ERROR_DIR . '/' . basename($file));
        continue;
    }

    echo " -> Texto extraído (" . strlen($text) . " chars). Convertendo com IA...\n";

    // 2. Chamada API com Retry
    $maxRetries = 3;
    $attempt = 0;
    $data = null;
    $success = false;

    while ($attempt < $maxRetries) {
        $attempt++;
        $data = $importService->analyzeWithAI($text, $apiKey);

        if (isset($data['error'])) {
            // Verifica se é erro 429 (Rate Limit)
            if (strpos($data['error'], '429') !== false || strpos($data['error'], 'Resource has been exhausted') !== false) {
                echo " [!] Rate Limit (429) na tentativa $attempt. Esperando 30s...\n";
                sleep(30);
                continue;
            }
        }

        if ($data && !isset($data['error'])) {
            $success = true;
            break;
        }

        // Se for outro erro, ou falha sem 429, não retenta (ou retenta? melhor não gastar quota)
        break;
    }

    if (!$success) {
        echo " [x] Erro na API após $attempt tentativas: " . ($data['error'] ?? 'Resposta vazia') . "\n";
        rename($file, $ERROR_DIR . '/' . basename($file));
        continue;
    }

    // 3. Importação para o DB
    try {
        $result = $importService->saveImportedData($data);

        echo " [v] Sucesso! Movendo arquivo.\n";
        rename($file, $SUCCESS_DIR . '/' . basename($file));

    } catch (Exception $e) {
        echo " [x] Erro DB: " . $e->getMessage() . "\n";
        rename($file, $ERROR_DIR . '/' . basename($file));
    }

    sleep(2);
}

echo "\nFim do processamento.\n";
