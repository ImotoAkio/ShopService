<?php

namespace App\Controllers;

use App\Core\Database;

class OrcamentoController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . \BASE_URL . '/login');
            exit;
        }
    }

    public function index()
    {
        $db = Database::getInstance()->getConnection();

        $sql = "
            SELECT o.*, c.name as client_name,
            (
                SELECT COALESCE(SUM(i.labor_total_cost), 0)
                FROM orcamento_itens i
                JOIN orcamento_zonas z ON i.zona_id = z.id
                JOIN orcamento_grupos g ON z.grupo_id = g.id
                WHERE g.orcamento_id = o.id
            ) as total
            FROM orcamentos o 
            JOIN clientes c ON o.client_id = c.id 
            WHERE 1=1
        ";
        $params = [];

        // Filter by Client
        if (!empty($_GET['client'])) {
            $sql .= " AND c.name LIKE ?";
            $params[] = '%' . $_GET['client'] . '%';
        }

        // Filter by Status
        if (!empty($_GET['status'])) {
            $sql .= " AND o.status = ?";
            $params[] = $_GET['status'];
        }

        // Filter by Date Range
        if (!empty($_GET['date_start'])) {
            $sql .= " AND DATE(o.created_at) >= ?";
            $params[] = $_GET['date_start'];
        }
        if (!empty($_GET['date_end'])) {
            $sql .= " AND DATE(o.created_at) <= ?";
            $params[] = $_GET['date_end'];
        }

        // Filter by Valve Model (Text Search)
        if (!empty($_GET['model'])) {
            $sql .= " AND (o.servico_descricao LIKE ? OR o.assunto LIKE ?)";
            $modelName = $_GET['model'];
            $params[] = '%' . $modelName . '%';
            $params[] = '%' . $modelName . '%';
        }

        $sql .= " ORDER BY o.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $orcamentos = $stmt->fetchAll();

        // Fetch Valve Models for Filter
        // We need to fetch them to populate the dropdown
        $stmtVRP = $db->query("SELECT * FROM valve_models ORDER BY name ASC");
        $valve_models = $stmtVRP->fetchAll();

        $viewContent = __DIR__ . '/../../views/orcamentos/index.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function create()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM clientes ORDER BY name ASC");
        $clientes = $stmt->fetchAll();

        // Check param for mode
        $mode = $_GET['mode'] ?? 'simple';

        // Fetch Open OSs
        $stmtOS = $db->query("
            SELECT os.*, c.name as client_name 
            FROM ordens_servico os 
            JOIN clientes c ON os.client_id = c.id 
            WHERE os.status != 'Concluído' 
            ORDER BY os.created_at DESC
        ");
        $oss = $stmtOS->fetchAll();

        // Fetch Valve Models
        $stmtVRP = $db->query("SELECT * FROM valve_models ORDER BY name ASC");
        $valve_models = $stmtVRP->fetchAll();

        // Handle Duplication / Base ID
        $base_orcamento = null;
        $base_itens = [];
        if (!empty($_GET['base_id'])) {
            $stmtBase = $db->prepare("SELECT * FROM orcamentos WHERE id = ?");
            $stmtBase->execute([$_GET['base_id']]);
            $base_orcamento = $stmtBase->fetch();

            if ($base_orcamento) {
                $stmtItems = $db->prepare("SELECT * FROM orcamento_itens WHERE orcamento_id = ?");
                $stmtItems->execute([$_GET['base_id']]);
                $base_itens = $stmtItems->fetchAll();
            }
        }

        $viewContent = __DIR__ . '/../../views/orcamentos/create.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function create_simple()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM clientes ORDER BY name ASC");
        $clientes = $stmt->fetchAll();

        // Fetch Budget Templates (formerly Valve Models)
        $stmtTemplates = $db->query("SELECT * FROM valve_models ORDER BY name ASC");
        $templates = $stmtTemplates->fetchAll();

        // Check for Replication Base OR Edit Mode
        $base_content = '';
        $base_total = '0.00';
        $edit_mode = false;
        $orcamento_id = null;
        $selected_client_id = null;

        if (!empty($_GET['edit_id'])) {
            $stmtEdit = $db->prepare("SELECT * FROM orcamentos WHERE id = ?");
            $stmtEdit->execute([$_GET['edit_id']]);
            $editData = $stmtEdit->fetch();
            if ($editData) {
                $base_content = $editData['servico_descricao'];
                $base_total = $editData['total'];
                $selected_client_id = $editData['client_id'];
                $orcamento_id = $editData['id'];
                $edit_mode = true;
            }
        } elseif (!empty($_GET['base_id'])) {
            $stmtBase = $db->prepare("SELECT servico_descricao, total FROM orcamentos WHERE id = ?");
            $stmtBase->execute([$_GET['base_id']]);
            $base = $stmtBase->fetch();
            if ($base) {
                $base_content = $base['servico_descricao'];
                $base_total = $base['total'];
            }
        }

        $viewContent = __DIR__ . '/../../views/orcamentos/create_simple.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function store_simple()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/orcamentos/nova-versao');
            exit;
        }

        $client_id = $_POST['client_id'] ?? null;
        $content = $_POST['content_html'] ?? '';
        $total = $_POST['total'] ?? 0;
        $user_id = $_SESSION['user_id'];
        $id = $_POST['id'] ?? null; // ID for Update

        // Extracted Fields for structured data (Metadata)
        $garantia = $_POST['garantia'] ?? '';
        $validade = $_POST['validade'] ?? '';
        $pagamento = $_POST['forma_pagamento'] ?? '';
        $observacoes = $_POST['observacoes'] ?? ''; // Currently used for extra notes in DB
        $procedimentos = $_POST['procedimentos'] ?? ''; // Can be extracted if needed
        $assunto = $_POST['assunto'] ?? '';

        if (!$client_id) {
            $_SESSION['error'] = 'Selecione um cliente.';
            $redirect = $id ? '/orcamentos/nova-versao?edit_id=' . $id : '/orcamentos/nova-versao';
            header('Location: ' . \BASE_URL . $redirect);
            exit;
        }

        $db = Database::getInstance()->getConnection();

        try {
            if ($id) {
                // UPDATE Logic
                $stmt = $db->prepare("UPDATE orcamentos SET client_id = ?, total = ?, servico_descricao = ?, garantia = ?, validade = ?, forma_pagamento = ?, observacoes = ?, assunto = ? WHERE id = ?");
                $stmt->execute([
                    $client_id,
                    $total,
                    $content,
                    $garantia,
                    $validade,
                    $pagamento,
                    $observacoes,
                    $assunto,
                    $id
                ]);
                $_SESSION['success'] = 'Orçamento atualizado com sucesso!';
            } else {
                // INSERT Logic
                $stmt = $db->prepare("INSERT INTO orcamentos (client_id, user_id, status, total, servico_descricao, garantia, validade, forma_pagamento, observacoes, assunto, created_at) VALUES (?, ?, 'Pendente', ?, ?, ?, ?, ?, ?, ?, NOW())");
                $stmt->execute([
                    $client_id,
                    $user_id,
                    $total,
                    $content,
                    $garantia,
                    $validade,
                    $pagamento,
                    $observacoes,
                    $assunto
                ]);
                $_SESSION['success'] = 'Orçamento criado com sucesso!';
            }

            header('Location: ' . \BASE_URL . '/orcamentos');
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao salvar: ' . $e->getMessage();
            $redirect = $id ? '/orcamentos/nova-versao?edit_id=' . $id : '/orcamentos/nova-versao';
            header('Location: ' . \BASE_URL . $redirect);
            exit;
        }
    }

    public function duplicar($id)
    {
        // Simple redirect to standard create page with base_id
        header('Location: ' . \BASE_URL . '/orcamentos/criar?base_id=' . $id);
        exit;
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/orcamentos/criar');
            exit;
        }

        $client_id = $_POST['client_id'] ?? null;
        $itens_raw = $_POST['itens'] ?? []; // Array of arrays
        // Re-organize POST array if needed, but name="itens[][field]" usually yields array of arrays directly
        // Just need to handle it based on PHP's parsing.

        /* 
           PHP parser for name="itens[][descricao]" usually results in:
           $_POST['itens'] = [
               [ 'descricao' => '...', 'quantidade' => '...', ... ],
               ...
           ]
           IF we structured HTML as name="itens[0][descricao]".
           BUT with name="itens[][descricao]", PHP creates:
           $_POST['itens'] = [
                0 => [ 'descricao' => '...' ],
                ... (wait, actually grouping might be tricky with [] on both)

           Let's Correct the HTML name strategy in view or handle it here.
           Strategy: name="descricao[]", name="quantidade[]" etc is easier to iterate.
           OR name="itens[index][field]" via JS index.

           Let's assume the view uses name="itens[][descricao]" which DOES NOT group by row automatically in PHP standard.
           It creates $_POST['itens'][0]['descricao'] if index is explicit or 
           $_POST['itens'][row_index]['field'] ? No.

           Wait, name="itens[][descricao]" gives $_POST['itens'][0]['descricao']? No.
           It gives $_POST['itens'] as an array of rows?
           Actually, `name="itens[][key]"` yields a structure like:
           itens[0][key], itens[1][key].
           Yes, provided each row has the same index. But `[]` auto-increments for each input encountered.
           So `descricao` row 1 gets index 0. `quantidade` row 1 gets index 0.
           IF they appear in order? No, PHP counters are per-name-level.

           BETTER APPROACH: name="descricao[]", name="quantidade[]".
           Then iterate by index.

           Let's check the View I just wrote:
           name="itens[][descricao]"
           This effectively creates `itens` array where each element is an array? 
           Check: `itens[0] => "desc"`, then `itens[1] => "desc"`? No used `[][key]`.
           PHP transforms `name="a[][b]"` into `a[0][b]`, `a[1][b]`.
           This works perfectly IF inputs are grouped?
           Actually, `a[][b]` -> creating a new index for `a` every time?
           Yes. So row 1 desc -> `a[0][b]`. Row 1 qty -> `a[1][c]`?
           THIS IS RISKY.

           FIX: I will update the View to use manual indexing OR use separate arrays `descricao[]`, `quantidade[]`.
           Let's use separate arrays in the view update, it's safer.
        */

        // Wait, I can't update the View in the same turn easily if I already wrote it? 
        // I wrote it in the current turn. I can re-write it correctly or handle parsing.

        // Let's handle parsing if using `descricao[]`. Update view first.
        // Actually, let's update Controller to expect `descricao[]`.

        // ... proceeding to read separate arrays ...
        $descricoes = $_POST['descricao'] ?? [];
        $quantidades = $_POST['quantidade'] ?? [];
        $valores = $_POST['valor_unitario'] ?? [];
        $os_id = !empty($_POST['os_id']) ? $_POST['os_id'] : null;
        $user_id = $_SESSION['user_id'];

        if (!$client_id || empty($descricoes)) {
            $_SESSION['error'] = 'Preencha os campos e adicione itens.';
            header('Location: ' . \BASE_URL . '/orcamentos/criar');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            // Calculate Total
            $total_orcamento = 0;
            $itens_to_insert = [];

            for ($i = 0; $i < count($descricoes); $i++) {
                $desc = $descricoes[$i];
                $qtd = $quantidades[$i];
                $val = $valores[$i];
                $subtotal = $qtd * $val;
                $total_orcamento += $subtotal;
                $itens_to_insert[] = [
                    'desc' => $desc,
                    'qtd' => $qtd,
                    'val' => $val,
                    'total' => $subtotal
                ];
            }

            // Insert Budget
            $stmt = $db->prepare("INSERT INTO orcamentos (client_id, user_id, status, total, os_id, assunto, servico_descricao, procedimentos, duracao, garantia, forma_pagamento, observacoes, validade) VALUES (?, ?, 'Pendente', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $client_id,
                $user_id,
                $total_orcamento,
                $os_id,
                $_POST['assunto'] ?? '',
                $_POST['servico_descricao'] ?? '',
                $_POST['procedimentos'] ?? '',
                $_POST['duracao'] ?? '',
                $_POST['garantia'] ?? '',
                $_POST['forma_pagamento'] ?? '',
                $_POST['observacoes'] ?? '',
                $_POST['validade'] ?? ''
            ]);
            $orcamento_id = $db->lastInsertId();

            if ($os_id) {
                $stmtSync = $db->prepare("UPDATE ordens_servico SET orcamento_id = ? WHERE id = ?");
                $stmtSync->execute([$orcamento_id, $os_id]);
            }

            // Insert Items
            $stmtItem = $db->prepare("INSERT INTO orcamento_itens (orcamento_id, description, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
            foreach ($itens_to_insert as $item) {
                $stmtItem->execute([$orcamento_id, $item['desc'], $item['qtd'], $item['val'], $item['total']]);
            }

            $db->commit();

            $_SESSION['success'] = 'Orçamento criado com sucesso!';
            // Redirect to details
            header('Location: ' . \BASE_URL . '/orcamentos/detalhes/' . $orcamento_id);
            exit;

        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Erro ao salvar orçamento: ' . $e->getMessage();
            header('Location: ' . \BASE_URL . '/orcamentos/criar');
            exit;
        }
    }

    public function edit($id)
    {
        $db = Database::getInstance()->getConnection();

        // Fetch Orcamento
        $stmt = $db->prepare("SELECT * FROM orcamentos WHERE id = ?");
        $stmt->execute([$id]);
        $orcamento = $stmt->fetch();

        if (!$orcamento) {
            $_SESSION['error'] = 'Orçamento não encontrado.';
            header('Location: ' . \BASE_URL . '/orcamentos');
            exit;
        }

        // Check if this is a "Simple/Text" budget
        // Heuristic: If servico_descricao contains HTML tags (Summernote always wraps in <p> or <div>)
        // AND validation logic. 
        $isSimpleMode = (strpos($orcamento['servico_descricao'], '<') !== false) && (strpos($orcamento['servico_descricao'], '>') !== false);

        if ($isSimpleMode) {
            // Redirect to Simple Editor with Edit Mode
            // We use ?id=X to indicate edit mode, or repurpose create_simple with data
            // Let's create a new method 'edit_simple' or just handle it in create_simple via param.
            // Keeping it clean: redirect to a specific action 'edit_simple_view'
            header('Location: ' . \BASE_URL . '/orcamentos/nova-versao?edit_id=' . $id);
            exit;
        }

        // Fetch Clients
        $stmt = $db->query("SELECT * FROM clientes ORDER BY name ASC");
        $clientes = $stmt->fetchAll();

        // Fetch Open OSs
        $stmtOS = $db->query("
            SELECT os.*, c.name as client_name 
            FROM ordens_servico os 
            JOIN clientes c ON os.client_id = c.id 
            WHERE os.status != 'Concluído' 
            ORDER BY os.created_at DESC
        ");
        $oss = $stmtOS->fetchAll();

        // Fetch Valve Models
        $stmtVRP = $db->query("SELECT * FROM valve_models ORDER BY name ASC");
        $valve_models = $stmtVRP->fetchAll();

        // Fetch Items
        $stmtItems = $db->prepare("SELECT * FROM orcamento_itens WHERE orcamento_id = ?");
        $stmtItems->execute([$id]);
        $itens = $stmtItems->fetchAll();

        $viewContent = __DIR__ . '/../../views/orcamentos/edit.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/orcamentos');
            exit;
        }

        $id = $_POST['id'] ?? null;
        $client_id = $_POST['client_id'] ?? null;
        $descricoes = $_POST['descricao'] ?? [];
        $quantidades = $_POST['quantidade'] ?? [];
        $valores = $_POST['valor_unitario'] ?? [];
        $os_id = !empty($_POST['os_id']) ? $_POST['os_id'] : null;

        if (!$id || !$client_id || empty($descricoes)) {
            $_SESSION['error'] = 'Preencha os campos e adicione itens.';
            header('Location: ' . \BASE_URL . '/orcamentos/edit/' . $id);
            exit;
        }

        $db = Database::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            // Calculate Total
            $total_orcamento = 0;
            $itens_to_insert = [];

            for ($i = 0; $i < count($descricoes); $i++) {
                $desc = $descricoes[$i];
                $qtd = $quantidades[$i];
                $val = $valores[$i];
                $subtotal = $qtd * $val;
                $total_orcamento += $subtotal;
                $itens_to_insert[] = [
                    'desc' => $desc,
                    'qtd' => $qtd,
                    'val' => $val,
                    'total' => $subtotal
                ];
            }

            // Update Budget
            $stmt = $db->prepare("UPDATE orcamentos SET client_id = ?, total = ?, os_id = ?, assunto = ?, servico_descricao = ?, procedimentos = ?, duracao = ?, garantia = ?, forma_pagamento = ?, observacoes = ?, validade = ? WHERE id = ?");
            $stmt->execute([
                $client_id,
                $total_orcamento,
                $os_id,
                $_POST['assunto'] ?? '',
                $_POST['servico_descricao'] ?? '',
                $_POST['procedimentos'] ?? '',
                $_POST['duracao'] ?? '',
                $_POST['garantia'] ?? '',
                $_POST['forma_pagamento'] ?? '',
                $_POST['observacoes'] ?? '',
                $_POST['validade'] ?? '',
                $id
            ]);

            // Sync OS if needed
            if ($os_id) {
                $stmtSync = $db->prepare("UPDATE ordens_servico SET orcamento_id = ? WHERE id = ?");
                $stmtSync->execute([$id, $os_id]);
            }

            // Replace Items
            $db->prepare("DELETE FROM orcamento_itens WHERE orcamento_id = ?")->execute([$id]);

            $stmtItem = $db->prepare("INSERT INTO orcamento_itens (orcamento_id, description, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?)");
            foreach ($itens_to_insert as $item) {
                $stmtItem->execute([$id, $item['desc'], $item['qtd'], $item['val'], $item['total']]);
            }

            $db->commit();

            $_SESSION['success'] = 'Orçamento atualizado com sucesso!';
            header('Location: ' . \BASE_URL . '/orcamentos/detalhes/' . $id);
            exit;

        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Erro ao atualizar orçamento: ' . $e->getMessage();
            header('Location: ' . \BASE_URL . '/orcamentos/edit/' . $id);
            exit;
        }
    }

    public function show($id)
    {
        $db = Database::getInstance()->getConnection();

        // Fetch Orcamento with Client info
        $stmt = $db->prepare("
            SELECT o.*, 
                   c.name as client_name, c.email as client_email, c.phone as client_phone, c.address as client_address,
                   c.documento as client_doc, c.cnpj as client_cnpj
            FROM orcamentos o
            JOIN clientes c ON o.client_id = c.id
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        $orcamento = $stmt->fetch();

        if (!$orcamento) {
            $_SESSION['error'] = 'Orçamento não encontrado.';
            header('Location: ' . \BASE_URL . '/orcamentos');
            exit;
        }

        // Fetch Items
        $stmtItems = $db->prepare("SELECT * FROM orcamento_itens WHERE orcamento_id = ?");
        $stmtItems->execute([$id]);
        $itens = $stmtItems->fetchAll();

        $viewContent = __DIR__ . '/../../views/orcamentos/show.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function gerarPdf($id)
    {
        if (!class_exists('Dompdf\Dompdf')) {
            die("Biblioteca Dompdf não encontrada. Instale via Composer.");
        }

        $db = Database::getInstance()->getConnection();

        // Fetch Orcamento with Client info
        $stmt = $db->prepare("
            SELECT o.*, 
                   c.name as client_name, c.address as client_address, c.documento as client_doc, c.cnpj as client_cnpj, c.email as client_email, c.phone as client_phone,
                   c.responsavel as client_resp, c.cargo as client_cargo, c.telefone2 as client_tel2
            FROM orcamentos o
            JOIN clientes c ON o.client_id = c.id
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        $orcamento = $stmt->fetch();

        if (!$orcamento) {
            die("Orçamento não encontrado.");
        }

        // Fetch Items (optional, if we want to list them or just allow the summary)
        $stmtItems = $db->prepare("SELECT * FROM orcamento_itens WHERE orcamento_id = ?");
        $stmtItems->execute([$id]);
        $itens = $stmtItems->fetchAll();

        // Prepare Data
        $data = date('d/m/Y', strtotime($orcamento['created_at']));
        // Format Total
        $totalFormatted = number_format($orcamento['total'], 2, ',', '.');

        // Use CNPJ if available, else Documento
        $cnpj = $orcamento['client_cnpj'] ? $orcamento['client_cnpj'] : $orcamento['client_doc'];

        $logoPath = __DIR__ . '/../../public/assets/img/logo.jpg';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/jpeg;base64,' . $logoData;
        } else {
            $logoBase64 = '';
        }

        // Date localization
        $months = [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro'
        ];
        $timestamp = strtotime($orcamento['created_at']);
        $day = date('d', $timestamp);
        $monthNum = (int) date('m', $timestamp);
        $year = date('Y', $timestamp);
        $dateExtenso = "$day de {$months[$monthNum]} de $year";

        // CHECK FOR SIMPLE MODE CONTENT
        $isSimpleMode = (strpos($orcamento['servico_descricao'], '<') !== false) && (strpos($orcamento['servico_descricao'], '>') !== false);

        // Base CSS
        $css = '
            @page { margin: 50px; }
            body { font-family: "Helvetica", sans-serif; font-size: 11pt; color: #000; line-height: 1.3; }
            .logo-img { max-width: 300px; height: auto; margin-bottom: 20px; }
            
            /* Standard Mode Styles */
            .header { text-align: left; margin-bottom: 20px; }
            .header-details { font-size: 9pt; margin-top: 10px; }
            .header-details a { color: blue; text-decoration: underline; }
            .info-block { margin-top: 20px; width: 100%; border-collapse: collapse; }
            .info-block td { vertical-align: top; }
            /* MANDATORY FORMATTING: Bold and Underlined Headers */
            .section-title { text-decoration: underline; font-weight: bold; font-style: italic; margin-top: 15px; margin-bottom: 5px; font-size: 11pt; }
            /* MANDATORY FORMATTING: Yellow Highlight */
            .highlight { background-color: #FFFF00; }
            .content-block { margin-bottom: 15px; }
            ul { margin: 5px 0 15px 20px; padding: 0; }
            li { margin-bottom: 3px; }
            .footer { margin-top: 30px; }
            .signature { border-top: 1px solid #000; width: 300px; margin-top: 50px; }
            
            /* Simple Mode Overrides */
            .simple-content { font-family: "Helvetica", sans-serif; }
            .simple-content p { margin-bottom: 10px; }
        ';

        $html = '<html><head><style>' . $css . '</style></head><body>';

        if ($isSimpleMode) {
            $headerHtml = '
            <div class="header">
                <img src="' . $logoBase64 . '" class="logo-img" alt="ShopService Logo">
                <div class="header-details">
                    MIK – SERVIÇOS HIDRAULICOS LTDA<br>
                    Av dos Imarés, 1383 – Indianópolis – São Paulo -SP<br>
                    Tel: (11) 5579-0835 / (11) 99376-4733<br>
                    <a href="http://shopservicevalvularedutora.com.br">shopservicevalvularedutora.com.br</a><br>
                    <a href="mailto:mik@shopservicevalvularedutora.com.br">mik@shopservicevalvularedutora.com.br</a>
                </div>
            </div>';

            $html .= $headerHtml;
            // Use the raw HTML content from Summernote
            $html .= '<div class="simple-content">' . $orcamento['servico_descricao'] . '</div>';

            // Add Total if present
            if ($orcamento['total'] > 0) {
                $html .= '
                <div class="content-block" style="text-align: right; margin-top: 40px; border-top: 1px solid #ccc; pt-2;">
                    <strong>TOTAL: R$ ' . number_format($orcamento['total'], 2, ',', '.') . '</strong>
                </div>';
            }

            $html .= '
            <div class="footer">
                <div class="signature">
                    Atenciosamente,<br>
                    Departamento Técnico
                </div>
            </div>
            </body></html>';

            // Early return for simple mode to avoid legacy logic
            $dompdf = new \Dompdf\Dompdf(['enable_remote' => true]);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->loadHtml($html);
            $dompdf->render();
            $dompdf->stream("orcamento_{$id}.pdf", ["Attachment" => false]);
            return;
        }

        // CHECK FOR HIERARCHICAL V2 CONTENT
        // Fetch Sectors to see if V2
        $stmtSectors = $db->prepare("SELECT * FROM orcamento_setores WHERE orcamento_id = ? ORDER BY id ASC");
        $stmtSectors->execute([$id]);
        $sectors = $stmtSectors->fetchAll();

        if (count($sectors) > 0) {
            // Render V2 PDF
            $html .= $this->generateV2PdfHtml($orcamento, $sectors, $db, $logoBase64, $dateExtenso, $cnpj);

            $dompdf = new \Dompdf\Dompdf(['enable_remote' => true]);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->loadHtml($html);
            $dompdf->render();
            $dompdf->stream("orcamento_v2_{$id}.pdf", ["Attachment" => false]);
            return;
        }

        // --- PROCESSING HIGHLIGHTS ---

        // 1. Disclaimer in Procedures
        $procRaw = $orcamento['procedimentos'];
        $procSafe = htmlspecialchars($procRaw);
        // Match specific recurring phrases or just strict replace if consistent
        $warningTxt = "Não nos responsabilizamos por avarias dos registros";
        // Flexible highlighting for the warning paragraph
        // Regex to match the parenthesis block roughly or just the text
        $procFormatted = preg_replace(
            '/(\(Não nos responsabilizamos.*?desgaste da peça\.\))/is',
            '<span class="highlight">$1</span>',
            $procSafe
        );
        $formattedProcedimentos = nl2br($procFormatted);

        // 2. Observations Highlights
        $obsRaw = $orcamento['observacoes'];
        $obsSafe = htmlspecialchars($obsRaw);

        // Highlight "Todos os serviços serão executados..." block
        // We'll mark from "Todos os serviços..." to the end of that block or file?
        // Let's highlight specific lines that usually appear.
        $highlightPhrases = [
            'Todos os serviços serão executados:',
            'Por funcionários em regime de CLT',
            'Empresa possuidora de seguro de sinistro de obra',
            'Funcionários devidamente treinados',
            'Workshop nas fabricas'
        ];

        foreach ($highlightPhrases as $phrase) {
            $obsSafe = str_ireplace($phrase, '<span class="highlight">' . $phrase . '</span>', $obsSafe);
        }

        // Highlight Compliance with NBR
        $obsSafe = preg_replace(
            '/(Cumprindo com as exigências da NBR.*?avariadas\.)/is',
            '<span class="highlight">$1</span>',
            $obsSafe
        );

        $formattedObservacoes = nl2br($obsSafe);

        // 3. Warranty Block (Garantia)
        // Usually stored in 'garantia' column, but sometimes repeated in text.
        // We will highlight the dedicated warranty block in HTML.

        $html = '
        <html>
        <head>
            <style>
                @page { margin: 50px; }
                body { font-family: "Helvetica", sans-serif; font-size: 11pt; color: #000; line-height: 1.3; }
                .header { text-align: left; margin-bottom: 20px; }
                
                /* Logo Styling */
                .logo-img { max-width: 300px; height: auto; }

                .header-details { font-size: 9pt; margin-top: 10px; }
                .header-details a { color: blue; text-decoration: underline; }
                
                .info-block { margin-top: 20px; width: 100%; border-collapse: collapse; }
                .info-block td { vertical-align: top; }
                
                .section-title { text-decoration: underline; font-weight: bold; font-style: italic; margin-top: 15px; margin-bottom: 5px; font-size: 11pt; }
                
                .highlight { background-color: #FFFF00; }
                
                .content-block { margin-bottom: 15px; }
                
                ul { margin: 5px 0 15px 20px; padding: 0; }
                li { margin-bottom: 3px; }

                .footer { margin-top: 30px; }
                .signature { border-top: 1px solid #000; width: 300px; margin-top: 50px; }
            </style>
        </head>
        <body>
            <div class="header">
                <img src="' . $logoBase64 . '" class="logo-img" alt="ShopService Logo">
                <div class="header-details">
                    MIK – SERVIÇOS HIDRAULICOS LTDA<br>
                    Av dos Imarés, 1383 – Indianópolis – São Paulo -SP<br>
                    Tel: (11) 5579-0835 / (11) 99376-4733<br>
                    <a href="http://shopservicevalvularedutora.com.br">shopservicevalvularedutora.com.br</a><br>
                    <a href="mailto:mik@shopservicevalvularedutora.com.br">mik@shopservicevalvularedutora.com.br</a>
                </div>
            </div>

            <div style="text-align: right; margin-bottom: 20px;">
                Orç. ' . number_format($orcamento['id'], 0, '', '.') . '/' . date('Y') . '<br>
                MI.<br>
                São Paulo, ' . $dateExtenso . '.
            </div>

            <div class="content-block">
                <strong>' . htmlspecialchars($orcamento['client_name']) . '</strong><br>
                ' . htmlspecialchars($orcamento['client_address']) . '<br>
                <strong>CNPJ: ' . htmlspecialchars($cnpj) . '</strong>
            </div>

            <div class="content-block">
                A/C<br>
                Sr(a). ' . htmlspecialchars($orcamento['client_resp']) . (!empty($orcamento['client_cargo']) ? ' (' . $orcamento['client_cargo'] . ')' : '') . '.<br>
                Tel: ' . htmlspecialchars($orcamento['client_phone']) . (!empty($orcamento['client_tel2']) ? ' / ' . $orcamento['client_tel2'] : '') . '.<br>
                Email ' . htmlspecialchars($orcamento['client_email']) . '
            </div>

            <div class="section-title">Assunto: ' . htmlspecialchars($orcamento['assunto']) . '</div>

            <div class="content-block">
                Prezado Senhor;<br>
                Conforme solicitação de VS temos a satisfação em colocar a disposição os nossos conhecimentos técnicos, para a execução dos serviços abaixo.
            </div>

            <div class="section-title">Serviço:</div>
            <div class="content-block">
                ' . nl2br(htmlspecialchars($orcamento['servico_descricao'])) . '
            </div>

            <div class="section-title">Procedimentos:</div>
            <div class="content-block">
                ' . $formattedProcedimentos . '
            </div>

            <div class="section-title">Duração:</div>
            <div class="content-block">' . htmlspecialchars($orcamento['duracao']) . '</div>

            <div class="section-title">Custo sem Art: Validade da proposta ' . htmlspecialchars($orcamento['validade']) . '</div>
            <div class="content-block">
                Manutenção ........................................................ R$ ' . $totalFormatted . '
            </div>

            <div class="content-block">
                <span class="highlight">Garantia de ' . htmlspecialchars($orcamento['garantia']) . ', exceto em caso de entupimento, sujeira nas válvulas, ar nas tubulações e peças avariadas. Conforme NBR 5626, a verificação do funcionamento das válvulas redutoras de pressão deve ser semestral.</span>
            </div>

            ' . (!empty($orcamento['observacoes']) ? '
            <div class="section-title">Observações:</div>
            <div class="content-block">
                ' . $formattedObservacoes . '
            </div>' : '') . '

            <div class="section-title">Forma de Pagamento: (sugestão)</div>
            <div class="content-block">
                ' . htmlspecialchars($orcamento['forma_pagamento']) . '
            </div>                ' . htmlspecialchars($orcamento['forma_pagamento']) . '
            </div>

            <div class="section-title">Considerações:</div>
            <div class="content-block">
                <ul>
                    <li>É de inteira responsabilidade da contratada, quaisquer danos causados e comprovado por nosso pessoal, porém não podemos nos responsabilizar por danos oriundo de vícios oculto ou que não respeitam ao isolamento do local de trabalho.</li>
                    <li>É de responsabilidade de a contratante comunicar aos condomínios a execução dos serviços, emitir boletim de informação referente à obra, isolar o local de trabalho e colocar sinalização de atenção, designar um funcionário de manutenção do prédio para, um local para guarda de ferramentas e material, bem com um vestiário e sanitários para uso do nosso pessoal.</li>
                    <li>O prazo para a execução dos serviços poderá ser reavaliado para mais ou para menos, no decorrer das atividades, em função das dificuldades encontradas.</li>
                    <li>Serviços e materiais não especificados nessa proposta será objeto de orçamento complementar.</li>
                </ul>
                <div class="highlight">
                Todos os serviços serão executados:<br>
                Por funcionários em regime de CLT, com todos os encargos recolhidos em dia que isenta o prédio de uma eventual ação trabalhista.<br>
                Empresa possuidora de seguro de sinistro de obra.<br>
                Funcionários devidamente treinados e possuidores de todas as certificações de segurança de trabalho.
                </div>
                <ul>
                    <li>Workshop nas fabricas.</li>
                    <li>Cumprindo com as exigências da NBR 5626 com garantia de 06 (seis) meses, exceto em caso de entupimento, sujeira nas válvulas, ar nas tubulações e peças avariadas.</li>
                    <li>Empresa desde 1982.</li>
                </ul>
                Sem mais para o momento ficamos a sua disposição para quaisquer outras dúvidas e esclarecimento que se fizer necessário.
            </div>

            <div class="footer">
                Atenciosamente,<br><br>
                <div class="signature">
                    MIK SERVIÇOS HIDRÁULICOS LTDA.<br>
                    Mauro Imoto.
                </div>
            </div>
        </body>
        </html>';

        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("Orcamento_" . $id . ".pdf", ["Attachment" => false]);
    }

    public function aprovar($id)
    {
        $db = Database::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            // Fetch Orcamento
            $stmt = $db->prepare("SELECT * FROM orcamentos WHERE id = ?");
            $stmt->execute([$id]);
            $orcamento = $stmt->fetch();

            if (!$orcamento || $orcamento['status'] !== 'Pendente') {
                throw new \Exception("Orçamento inválido ou já processado.");
            }

            // Mark as Approved
            $stmtUpd = $db->prepare("UPDATE orcamentos SET status = 'Aprovado' WHERE id = ?");
            $stmtUpd->execute([$id]);

            // Parse Validity (Garantia)
            // Example "06 (seis) meses" -> 6
            $validadeMeses = 0;
            if (!empty($orcamento['garantia'])) {
                if (preg_match('/(\d+)/', $orcamento['garantia'], $matches)) {
                    $validadeMeses = (int) $matches[1];
                }
            }

            // Build Initial Report from Budget Scope
            // We use strip_tags to convert HTML to text if OS relatorio is text-only, 
            // but the OS view uses a textarea which supports text. 
            // Let's keep it relatively clean.
            $scope = "Ordem de Serviço gerada a partir do Orçamento Aprovado #{$id}.\n\n";
            $scope .= "=== DESCRIÇÃO DO SERVIÇO ===\n";
            $scope .= strip_tags($orcamento['servico_descricao']) . "\n\n";

            if (!empty($orcamento['procedimentos'])) {
                $scope .= "=== PROCEDIMENTOS ===\n";
                $scope .= strip_tags($orcamento['procedimentos']) . "\n\n";
            }

            if (!empty($orcamento['observacoes'])) {
                $scope .= "=== OBSERVAÇÕES ===\n";
                $scope .= strip_tags($orcamento['observacoes']) . "\n";
            }

            // Create OS
            // Status: Aberto
            // Tipo: Execução
            $stmtOS = $db->prepare("INSERT INTO ordens_servico (client_id, user_id, status, relatorio, tipo, orcamento_id, validade_meses) VALUES (?, ?, 'Aberto', ?, 'Execução', ?, ?)");
            $stmtOS->execute([
                $orcamento['client_id'],
                $_SESSION['user_id'],
                $scope,
                $id,
                $validadeMeses
            ]);
            $os_id = $db->lastInsertId();

            // Link OS back to Orcamento
            $stmtLink = $db->prepare("UPDATE orcamentos SET os_id = ? WHERE id = ?");
            $stmtLink->execute([$os_id, $id]);

            $db->commit();

            $_SESSION['success'] = "Orçamento aprovado! OS #{$os_id} criada automaticamente.";
            // Redirect to OS Edit page to review
            header('Location: ' . \BASE_URL . '/os/edit/' . $os_id);
            exit;

        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = "Erro ao aprovar: " . $e->getMessage();
            header('Location: ' . \BASE_URL . '/orcamentos'); // Redirect to list
            exit;
        }
    }

    // --- IMPORT MODULE ---

    public function import()
    {
        $viewContent = __DIR__ . '/../../views/orcamentos/import.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    // uploadImport Removed - Deprecated


    public function processImport()
    {
        header('Content-Type: application/json');

        // Check if ImportService exists, if not require it (autoloader should handle but just in case)
        if (!class_exists('App\Services\ImportService')) {
            require_once __DIR__ . '/../Services/ImportService.php';
        }

        $inputData = json_decode(file_get_contents('php://input'), true);

        if (!$inputData) {
            echo json_encode(['success' => false, 'message' => 'JSON inválido ou vazio']);
            exit;
        }

        // Validate basic structure
        if (!isset($inputData['cliente']) && !isset($inputData['orcamentos'])) {
            echo json_encode(['success' => false, 'message' => 'JSON não segue o formato esperado (falta cliente ou orcamentos)']);
            exit;
        }

        $importService = new \App\Services\ImportService();

        // Direct DB Insertion (Bypass AI)
        try {
            $result = $importService->saveImportedData($inputData);
            echo json_encode(['success' => true, 'message' => 'Importado com sucesso!', 'data' => $result]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erro ao Salvar: ' . $e->getMessage()]);
        }
        exit;
    }

    public function create_v2()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM clientes ORDER BY name ASC");
        $clientes = $stmt->fetchAll();

        $viewContent = __DIR__ . '/../../views/orcamentos/create_v2.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function store_v2()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/orcamentos/create_v2');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        try {
            $db->beginTransaction();

            // 1. Proposal (Insert with 0 total initially)
            $stmt = $db->prepare("INSERT INTO orcamentos (client_id, user_id, status, total, assunto, garantia, validade, forma_pagamento, created_at) VALUES (?, ?, 'Pendente', 0.00, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $_POST['client_id'],
                $_SESSION['user_id'],
                $_POST['assunto'] ?? '',
                $_POST['garantia'] ?? '',
                $_POST['validade'] ?? '',
                $_POST['forma_pagamento'] ?? ''
            ]);
            $orcID = $db->lastInsertId();

            $calculatedTotal = 0.0;

            // 2. Sectors
            if (!empty($_POST['sectors'])) {
                $stmtSector = $db->prepare("INSERT INTO orcamento_setores (orcamento_id, name) VALUES (?, ?)");
                $stmtZone = $db->prepare("INSERT INTO orcamento_zonas (setor_id, name, pipeline_material, pressure_value, pressure_unit, floor_range) VALUES (?, ?, ?, ?, ?, ?)");
                $stmtItem = $db->prepare("INSERT INTO orcamento_itens (orcamento_id, zona_id, description, brand_model, diameter, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");

                foreach ($_POST['sectors'] as $sector) {
                    if (empty($sector['name']))
                        continue;
                    $stmtSector->execute([$orcID, $sector['name']]);
                    $sectorID = $db->lastInsertId();

                    if (!empty($sector['zones'])) {
                        foreach ($sector['zones'] as $zone) {
                            if (empty($zone['name']))
                                continue;
                            $pVal = str_replace(',', '.', $zone['pressure_value'] ?? '0');

                            $stmtZone->execute([
                                $sectorID,
                                $zone['name'],
                                $zone['pipeline_material'],
                                $pVal,
                                $zone['pressure_unit'] ?? null,
                                $zone['floor_range'] ?? null
                            ]);
                            $zoneID = $db->lastInsertId();

                            if (!empty($zone['items'])) {
                                foreach ($zone['items'] as $item) {
                                    $qty = floatval(str_replace(',', '.', $item['quantity'] ?? '0'));
                                    $unit = floatval(str_replace(',', '.', $item['unit_price'] ?? '0'));
                                    $sub = $qty * $unit;
                                    $calculatedTotal += $sub;

                                    $stmtItem->execute([
                                        $orcID,
                                        $zoneID,
                                        $item['description'],
                                        $item['brand_model'] ?? '',
                                        $item['diameter'] ?? '',
                                        $qty,
                                        $unit,
                                        $sub
                                    ]);
                                }
                            }
                        }
                    }
                }
            }

            // Update Final Total
            $db->prepare("UPDATE orcamentos SET total = ? WHERE id = ?")->execute([$calculatedTotal, $orcID]);

            $db->commit();
            $_SESSION['success'] = "Orçamento V2 (Hierárquico) criado com sucesso! ID: $orcID";
            header('Location: ' . \BASE_URL . '/orcamentos');
            exit;

        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Erro ao salvar: ' . $e->getMessage();
            header('Location: ' . \BASE_URL . '/orcamentos/create_v2');
            exit;
        }
    }

    private function generateV2PdfHtml($orcamento, $sectors, $db, $logoBase64, $dateExtenso, $cnpj)
    {
        $html = '';

        // HEADER
        $html .= '
            <div class="header">
                <table width="100%">
                    <tr>
                        <td width="30%"><img src="' . $logoBase64 . '" class="logo-img" alt="Logo"></td>
                        <td width="70%" class="header-details" style="text-align: right;">
                            <strong>MIK – SERVIÇOS HIDRAULICOS LTDA</strong><br>
                            Av dos Imarés, 1383 – Indianópolis – São Paulo -SP<br>
                            Tel: (11) 5579-0835 / (11) 99376-4733<br>
                            shopservicevalvularedutora.com.br<br>
                            mik@shopservicevalvularedutora.com.br
                        </td>
                    </tr>
                </table>
            </div>

            <div style="text-align: right; margin-bottom: 30px;">
                <strong>Orç. ' . number_format($orcamento['id'], 0, '', '.') . '/' . date('Y') . '</strong><br>
                São Paulo, ' . $dateExtenso . '.
            </div>
            
            <div class="content-block" style="border: 1px solid #000; padding: 10px; margin-bottom: 20px;">
                <strong>Cliente:</strong> ' . htmlspecialchars($orcamento['client_name']) . '<br>
                <strong>Endereço:</strong> ' . htmlspecialchars($orcamento['client_address']) . '<br>
                <strong>CNPJ/CPF:</strong> ' . htmlspecialchars($cnpj) . '<br>
                <strong>Assunto:</strong> ' . htmlspecialchars($orcamento['assunto']) . '
            </div>
            
            <div class="content-block">
                Prezados Senhores,<br><br>
                Apresentamos abaixo nossa proposta para execução dos serviços solicitados:
            </div>
        ';

        // SECTORS & ZONES Loop
        foreach ($sectors as $sector) {
            $html .= '<h3 style="background-color: #eee; padding: 5px; border-bottom: 2px solid #333; margin-top: 20px;">' . htmlspecialchars($sector['name']) . '</h3>';

            $stmtZones = $db->prepare("SELECT * FROM orcamento_zonas WHERE setor_id = ? ORDER BY id ASC");
            $stmtZones->execute([$sector['id']]);
            $zones = $stmtZones->fetchAll();

            foreach ($zones as $zone) {
                $html .= '<div style="margin-left: 20px; margin-bottom: 15px; border: 1px solid #ddd; padding: 10px;">';
                $html .= '<table width="100%" style="margin-bottom: 10px;">
                    <tr>
                        <td><strong>Zona:</strong> ' . htmlspecialchars($zone['name']) . '</td>
                        <td><strong>Material:</strong> ' . htmlspecialchars($zone['pipeline_material']) . '</td>
                    </tr>
                    <tr>
                        <td><strong>Pressão:</strong> ' . htmlspecialchars($zone['pressure_value']) . ' ' . htmlspecialchars($zone['pressure_unit']) . '</td>
                        <td><strong>Andares:</strong> ' . htmlspecialchars($zone['floor_range']) . '</td>
                    </tr>
                </table>';

                // ITEMS
                $stmtItems = $db->prepare("SELECT * FROM orcamento_itens WHERE zona_id = ? ORDER BY id ASC");
                $stmtItems->execute([$zone['id']]);
                $items = $stmtItems->fetchAll();

                if (count($items) > 0) {
                    $html .= '<table width="100%" style="border-collapse: collapse; font-size: 10pt;">
                        <tr style="background-color: #f9f9f9;">
                            <th style="border: 1px solid #ccc; padding: 4px; text-align: left;">Item / Descrição</th>
                            <th style="border: 1px solid #ccc; padding: 4px;">Marca/Modelo</th>
                            <th style="border: 1px solid #ccc; padding: 4px;">Ø</th>
                            <th style="border: 1px solid #ccc; padding: 4px;">Qtd</th>
                            <th style="border: 1px solid #ccc; padding: 4px;">Unit.</th>
                            <th style="border: 1px solid #ccc; padding: 4px;">Total</th>
                        </tr>';

                    foreach ($items as $item) {
                        $html .= '<tr>
                            <td style="border: 1px solid #ccc; padding: 4px;">' . htmlspecialchars($item['description']) . '</td>
                            <td style="border: 1px solid #ccc; padding: 4px;">' . htmlspecialchars($item['brand_model']) . '</td>
                            <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">' . htmlspecialchars($item['diameter']) . '</td>
                            <td style="border: 1px solid #ccc; padding: 4px; text-align: center;">' . number_format($item['quantity'], 2, ',', '.') . '</td>
                            <td style="border: 1px solid #ccc; padding: 4px; text-align: right;">R$ ' . number_format($item['unit_price'], 2, ',', '.') . '</td>
                            <td style="border: 1px solid #ccc; padding: 4px; text-align: right;">R$ ' . number_format($item['total_price'], 2, ',', '.') . '</td>
                         </tr>';
                    }
                    $html .= '</table>';
                } else {
                    $html .= '<em>Nenhum item adicionado nesta zona.</em>';
                }
                $html .= '</div>';
            }
        }

        // TOTAL & FOOTER
        $html .= '
            <div style="text-align: right; margin-top: 30px; font-size: 12pt; font-weight: bold; border-top: 2px solid #000; padding-top: 10px;">
                VALOR TOTAL DOS SERVIÇOS: R$ ' . number_format($orcamento['total'], 2, ',', '.') . '
            </div>
            
            <div style="margin-top: 30px;">
                <strong>Condições Comerciais:</strong><br>
                <ul>
                    <li><strong>Forma de Pagamento:</strong> ' . htmlspecialchars($orcamento['forma_pagamento']) . '</li>
                    <li><strong>Validade da Proposta:</strong> ' . htmlspecialchars($orcamento['validade']) . '</li>
                    <li><strong>Garantia:</strong> ' . htmlspecialchars($orcamento['garantia']) . '</li>
                </ul>
            </div>
            
            <div class="footer">
                <div class="signature">
                    Atenciosamente,<br>
                    Departamento Técnico<br>
                    ShopService Válvulas
                </div>
            </div>
            </body></html>
        ';

        return $html;
    }
}
