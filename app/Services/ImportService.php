<?php

namespace App\Services;

class ImportService
{
    /**
     * Extrai texto bruto de um arquivo .docx
     */
    public function extractTextFromDocx($filePath)
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $zip = new \ZipArchive;
        if ($zip->open($filePath) === TRUE) {
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

            $text = strip_tags($xmlData);
            $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
            $text = preg_replace('/\s+/', ' ', $text);

            return trim($text);
        } else {
            return false;
        }
    }

    /**
     * Envia texto para AI (Gemini ou OpenAI) estruturar
     */
    public function analyzeWithAI($text, $apiKey)
    {
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

        if (isset($jsonResp['candidates'][0]['content']['parts'][0]['text'])) {
            $rawContent = $jsonResp['candidates'][0]['content']['parts'][0]['text'];
            $rawContent = preg_replace('/^```json\s*/i', '', $rawContent);
            $rawContent = preg_replace('/^```\s*/i', '', $rawContent);
            $rawContent = preg_replace('/\s*```$/', '', $rawContent);
            return json_decode($rawContent, true);
        }

        return ['error' => 'Formato de resposta inesperado da API'];
    }

    public function getApiKey()
    {
        $key = $this->getEnvVar('OPENAI_API_KEY');
        if ($key)
            return $key;
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
        $db = \App\Core\Database::getInstance()->getConnection();

        try {
            $inTransaction = $db->inTransaction();
            if (!$inTransaction) {
                $db->beginTransaction();
            }

            // 1. Processa Cliente
            $c = $data['cliente'] ?? [];
            if (empty($c['name'])) {
                throw new \Exception("Nome do cliente não encontrado na resposta.");
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
                $stmtInsert = $db->prepare("INSERT INTO clientes (name, documento, cnpj, email, phone, address, contact_name, contact_role, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
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
            if (empty($orcamentos) && isset($data['assunto'])) {
                $orcamentos = [$data];
            }

            $lastOrcId = null;

            foreach ($orcamentos as $orc) {
                // INSERT Orcamento (English Col Names, no user_id, no total)
                // Includes 'procedures' now
                $sqlOrc = "INSERT INTO orcamentos (
                    client_id, status, subject, service_description, 
                    warranty, validity, payment_terms, observations, procedures,
                    date, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                $dateOrc = $orc['data_orcamento'] ?? date('Y-m-d');
                if (strtotime($dateOrc) === false)
                    $dateOrc = date('Y-m-d');

                $subject = $orc['assunto'] ?? 'Importado';
                $servico_descricao = $orc['servico_descricao'] ?? '';
                $procedimentos = $orc['procedimentos'] ?? '';
                $observacoes = $orc['observacoes'] ?? '';

                $stmtOrc = $db->prepare($sqlOrc);
                $stmtOrc->execute([
                    $clientId,
                    $orc['status'] ?? 'draft',
                    $subject,
                    $servico_descricao,
                    $orc['garantia'] ?? '',
                    $orc['validade'] ?? '',
                    $orc['forma_pagamento'] ?? '',
                    $observacoes,
                    $procedimentos,
                    $dateOrc
                ]);

                $lastOrcId = $db->lastInsertId();

                // 2.a Create/Update Valve Model (Template)
                if (!empty($subject)) {
                    $stmtCheckModel = $db->prepare("SELECT id FROM valve_models WHERE name = ?");
                    $stmtCheckModel->execute([$subject]);
                    $modelExists = $stmtCheckModel->fetch();

                    if (!$modelExists) {
                        $stmtModel = $db->prepare("INSERT INTO valve_models (name, service_description, procedures, observations, created_at) VALUES (?, ?, ?, ?, NOW())");
                        $stmtModel->execute([
                            $subject,
                            $servico_descricao,
                            $procedimentos,
                            $observacoes
                        ]);
                    }
                }

                // Itens (Hierarchy V2: Group -> Zone -> Item)
                if (!empty($orc['itens']) && is_array($orc['itens'])) {

                    // 1. Create Default Group
                    $stmtGroup = $db->prepare("INSERT INTO orcamento_grupos (orcamento_id, name) VALUES (?, ?)");
                    $stmtGroup->execute([$lastOrcId, 'Geral']);
                    $groupId = $db->lastInsertId();

                    // 2. Create Default Zone
                    $stmtZone = $db->prepare("INSERT INTO orcamento_zonas (grupo_id, name) VALUES (?, ?)");
                    $stmtZone->execute([$groupId, 'Geral']);
                    $zoneId = $db->lastInsertId();

                    // 3. Insert Items linked to Zone
                    $sqlItem = "INSERT INTO orcamento_itens (zona_id, name, quantity, labor_unit_cost) VALUES (?, ?, ?, ?)";
                    $stmtItem = $db->prepare($sqlItem);

                    foreach ($orc['itens'] as $item) {
                        $stmtItem->execute([
                            $zoneId,
                            $item['description'] ?? 'Item',
                            $item['quantity'] ?? 1,
                            $item['unit_price'] ?? 0 // Mapping unit_price to labor_unit_cost
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
            throw $e;
        }
    }
}
