<?php

namespace App\Services;

class ImportService
{
    /**
     * Extrai texto bruto de um arquivo .docx
     * 
     * @param string $filePath Caminho absoluto para o arquivo docx
     * @return string|false Texto extraído ou false se falhar
     */
    public function extractTextFromDocx($filePath)
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $zip = new \ZipArchive;
        if ($zip->open($filePath) === TRUE) {
            // Tenta ler o xml principal do word
            $xmlIndex = $zip->locateName('word/document.xml');
            if ($xmlIndex === false) {
                $zip->close();
                return false;
            }

            $xmlData = $zip->getFromName('word/document.xml');
            $zip->close();

            if ($xmlData === false) {
                return false;
            }

            // Limpeza simples de XML para extrair texto
            // Remove tags XML e deixa espaços
            $text = strip_tags($xmlData);

            // Decodifica entidades HTML se houver
            $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

            // Limpeza de espaços excessivos
            $text = preg_replace('/\s+/', ' ', $text);

            return trim($text);
        } else {
            return false;
        }
    }

    /**
     * Envia texto para AI (Gemini ou OpenAI) estruturar
     * 
     * @param string $text Texto bruto do orçamento
     * @param string $apiKey Chave da API
     * @return array|false Array com dados estruturados ou false/erro
     */
    public function analyzeWithAI($text, $apiKey)
    {
        // Detect Provider
        if (strpos($apiKey, 'sk-') === 0) {
            return $this->analyzeWithOpenAI($text, $apiKey);
        } else {
            return $this->analyzeWithGemini($text, $apiKey);
        }
    }

    private function analyzeWithOpenAI($text, $apiKey)
    {
        $url = "https://api.openai.com/v1/chat/completions";
        $systemPrompt = "Você é um assistente que estrutura dados de orçamentos brutos. Extraia para JSON conforme schema: { cliente: { name, documento, cnpj, email, phone, address, responsavel }, orcamentos: [ { assunto, status, total, data_orcamento, servico_descricao, garantia, validade, forma_pagamento, observacoes, itens: [ { description, quantity, unit_price, total_price } ] } ] }. Responda APENAS o JSON. Sem markdown.";

        $payload = [
            "model" => "gpt-4o-mini",
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => "TEXTO DO ORÇAMENTO:\n" . $text]
            ],
            "response_format" => ["type" => "json_object"],
            "temperature" => 0.1
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return ['error' => 'Curl error: ' . curl_error($ch)];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['error' => "OpenAI API Error ($httpCode): " . $response];
        }

        $jsonResp = json_decode($response, true);
        $content = $jsonResp['choices'][0]['message']['content'] ?? null;

        if ($content) {
            return json_decode($content, true);
        }

        return ['error' => 'Formato de resposta inesperado da OpenAI'];
    }

    private function analyzeWithGemini($text, $apiKey)
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

        $systemPrompt = "Instrução: \"Você é um assistente que estrutura dados de orçamentos brutos. Extraia para JSON conforme schema: { cliente: { name, documento, cnpj, email, phone, address, responsavel }, orcamentos: [ { assunto, status, total, data_orcamento, servico_descricao, garantia, validade, forma_pagamento, observacoes, itens: [ { description, quantity, unit_price, total_price } ] } ] }. Responda APENAS o JSON. Sem markdown.\"";

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $systemPrompt . "\n TEXTO DO ORÇAMENTO: " . $text]
                    ]
                ]
            ],
            "generationConfig" => [
                "responseMimeType" => "application/json"
            ]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return ['error' => 'Curl error: ' . curl_error($ch)];
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            return ['error' => "API Error ($httpCode): " . $response];
        }

        $jsonResp = json_decode($response, true);

        // Extrai o texto da resposta
        if (isset($jsonResp['candidates'][0]['content']['parts'][0]['text'])) {
            $rawContent = $jsonResp['candidates'][0]['content']['parts'][0]['text'];

            // Limpa Markdown se gemini mandar ```json ... ```
            $rawContent = preg_replace('/^```json\s*/i', '', $rawContent);
            $rawContent = preg_replace('/^```\s*/i', '', $rawContent); // caso mande so ```
            $rawContent = preg_replace('/\s*```$/', '', $rawContent);

            return json_decode($rawContent, true);
        }

        return ['error' => 'Formato de resposta inesperado da API'];
    }

    /**
     * Tenta obter a chave API do .env ou ambiente (OpenAI ou Gemini)
     */
    public function getApiKey()
    {
        // 1. Tenta OpenAI
        $key = $this->getEnvVar('OPENAI_API_KEY');
        if ($key)
            return $key;

        // 2. Tenta Gemini
        $key = $this->getEnvVar('GEMINI_API_KEY');
        if ($key)
            return $key;

        return null;
    }

    private function getEnvVar($varName)
    {
        $key = getenv($varName);
        if ($key && $key !== 'YOUR_API_KEY_HERE') {
            return $key;
        }
        $envPath = __DIR__ . '/../../.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0)
                    continue;
                list($name, $value) = explode('=', $line, 2);
                if (trim($name) === $varName) {
                    $val = trim($value);
                    if ($val && $val !== 'YOUR_API_KEY_HERE')
                        return $val;
                }
            }
        }
        return null;
    }

    /**
     * Salva os dados estruturados no banco de dados
     * 
     * @param array $data Dados retornados pela analyzeWithAI
     * @return array Resultado ['success' => bool, 'message' => string, 'orcamento_id' => int|null]
     */
    public function saveImportedData($data)
    {
        // Require Database if not already available (assuming singleton or autoload)
        // If Database class is not imported at top, we might need fully qualified name or to add 'use'.
        // Let's assume \App\Core\Database is available via autoloader, but better to check usage.

        $db = \App\Core\Database::getInstance()->getConnection();

        try {
            // Se já houver transação, não iniciamos outra
            $inTransaction = $db->inTransaction();
            if (!$inTransaction) {
                $db->beginTransaction();
            }

            // 1. Processa Cliente
            $c = $data['cliente'] ?? [];
            if (empty($c['name'])) {
                // Tenta recuperar se houver apenas 'orcamentos' e nenhum cliente na raiz...
                // Mas vamos assumir erro por enquanto.
                throw new \Exception("Nome do cliente não encontrado na resposta da IA.");
            }

            $clientId = null;

            // Check existence
            $stmtCheck = $db->prepare("SELECT id FROM clientes WHERE (cnpj IS NOT NULL AND cnpj != '' AND cnpj = ?) OR (documento IS NOT NULL AND documento != '' AND documento = ?) OR name = ?");
            $cnpj = $c['cnpj'] ?? '';
            $doc = $c['documento'] ?? '';
            $name = $c['name'];

            $stmtCheck->execute([$cnpj, $doc, $name]);
            $existing = $stmtCheck->fetch();

            if ($existing) {
                $clientId = $existing['id'];
            } else {
                $stmtInsert = $db->prepare("INSERT INTO clientes (name, documento, cnpj, email, phone, address, responsavel, cargo, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmtInsert->execute([
                    $c['name'],
                    $c['documento'] ?? '',
                    $c['cnpj'] ?? null,
                    $c['email'] ?? '',
                    $c['phone'] ?? '',
                    $c['address'] ?? '',
                    $c['responsavel'] ?? '',
                    'Síndico/Responsável'
                ]);
                $clientId = $db->lastInsertId();
            }

            // 2. Processa Orçamentos
            $orcamentos = $data['orcamentos'] ?? [];
            // Fallback: se 'orcamentos' estiver vazio, mas a raiz tiver dados de orçamento
            if (empty($orcamentos) && isset($data['assunto'])) {
                $orcamentos = [$data];
            }

            $lastOrcId = null;

            foreach ($orcamentos as $orc) {
                $sqlOrc = "INSERT INTO orcamentos (
                    client_id, user_id, status, total, servico_descricao, 
                    assunto, garantia, validade, forma_pagamento, observacoes, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

                $dateOrc = $orc['data_orcamento'] ?? date('Y-m-d');
                if (strtotime($dateOrc) === false)
                    $dateOrc = date('Y-m-d');

                $stmtOrc = $db->prepare($sqlOrc);
                $stmtOrc->execute([
                    $clientId,
                    1, // User Adm (Default)
                    $orc['status'] ?? 'Pendente',
                    $orc['total'] ?? 0,
                    $orc['servico_descricao'] ?? '',
                    $orc['assunto'] ?? 'Importado',
                    $orc['garantia'] ?? '',
                    $orc['validade'] ?? '',
                    $orc['forma_pagamento'] ?? '',
                    $orc['observacoes'] ?? '',
                    $dateOrc . ' 12:00:00'
                ]);

                $lastOrcId = $db->lastInsertId();

                // Itens
                if (!empty($orc['itens']) && is_array($orc['itens'])) {
                    $sqlItem = "INSERT INTO orcamento_itens (orcamento_id, description, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)";
                    $stmtItem = $db->prepare($sqlItem);

                    foreach ($orc['itens'] as $item) {
                        $stmtItem->execute([
                            $lastOrcId,
                            $item['description'] ?? 'Item',
                            $item['quantity'] ?? 1,
                            $item['unit_price'] ?? 0,
                            $item['total_price'] ?? 0
                        ]);
                    }
                }
            }

            if (!$inTransaction) {
                $db->commit();
            }

            return ['success' => true, 'message' => 'Dados importados com sucesso.', 'last_id' => $lastOrcId];

        } catch (\Exception $e) {
            if (!$inTransaction && $db->inTransaction()) {
                $db->rollBack();
            }
            throw $e; // Re-throw para ser tratado quem chamou ou retornar array de erro
        }
    }
}
