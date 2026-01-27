<?php

namespace App\Controllers;

use App\Core\Database;
use Dompdf\Dompdf; // Assuming Dompdf is installed via Composer

class OSController
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
            SELECT os.*, c.name as client_name 
            FROM ordens_servico os 
            JOIN clientes c ON os.client_id = c.id 
            WHERE 1=1
        ";
        $params = [];

        // Filter by Client Name
        if (!empty($_GET['client'])) {
            $sql .= " AND c.name LIKE ?";
            $params[] = '%' . $_GET['client'] . '%';
        }

        // Filter by Status
        if (!empty($_GET['status'])) {
            $sql .= " AND os.status = ?";
            $params[] = $_GET['status'];
        }

        // Filter by Date Range
        if (!empty($_GET['date_start'])) {
            $sql .= " AND DATE(os.created_at) >= ?";
            $params[] = $_GET['date_start'];
        }
        if (!empty($_GET['date_end'])) {
            $sql .= " AND DATE(os.created_at) <= ?";
            $params[] = $_GET['date_end'];
        }

        $sql .= " ORDER BY os.created_at DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $ordens = $stmt->fetchAll();

        $viewContent = __DIR__ . '/../../views/os/index.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function create()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM clientes ORDER BY name ASC");
        $clientes = $stmt->fetchAll();

        // Fetch Open/Pending Budgets
        // It's better to filter by client in JS, but for now fetch all pending logic?
        // Let's fetch all Pending budgets with Client Name, or just all Pending.
        // We will display them and maybe let user select.
        $stmtOrc = $db->query("
            SELECT o.*, c.name as client_name 
            FROM orcamentos o 
            JOIN clientes c ON o.client_id = c.id 
            WHERE o.status = 'Pendente' 
            ORDER BY o.created_at DESC
        ");
        $orcamentos = $stmtOrc->fetchAll();

        $viewContent = __DIR__ . '/../../views/os/create.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/os/criar');
            exit;
        }

        $client_id = $_POST['client_id'] ?? null;
        $status = $_POST['status'] ?? 'Aberto';
        $tipo = $_POST['tipo'] ?? 'Execução';
        $relatorio = $_POST['relatorio'] ?? '';
        $orcamento_id = !empty($_POST['orcamento_id']) ? $_POST['orcamento_id'] : null;
        $user_id = $_SESSION['user_id'];

        if (!$client_id || !$relatorio) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios.';
            header('Location: ' . \BASE_URL . '/os/criar');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        try {
            $validade_meses = !empty($_POST['validade_meses']) ? (int) $_POST['validade_meses'] : null;

            $aviso = $_POST['aviso'] ?? '';

            // Insert OS
            $stmt = $db->prepare("INSERT INTO ordens_servico (client_id, user_id, status, relatorio, orcamento_id, tipo, validade_meses, aviso) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$client_id, $user_id, $status, $relatorio, $orcamento_id, $tipo, $validade_meses, $aviso]);
            $os_id = $db->lastInsertId();

            if ($orcamento_id) {
                $stmtSync = $db->prepare("UPDATE orcamentos SET os_id = ? WHERE id = ?");
                $stmtSync->execute([$os_id, $orcamento_id]);
            }

            // Handle File Uploads
            if (isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
                $year = date('Y');
                $month = date('m');
                // Use absolute path for upload directory
                $targetDir = __DIR__ . '/../../public/uploads/' . $year . '/' . $month . '/';

                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $files = $_FILES['fotos'];
                $count = count($files['name']);

                $stmtPhoto = $db->prepare("INSERT INTO os_fotos (os_id, photo_path) VALUES (?, ?)");

                for ($i = 0; $i < $count; $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                        $filename = uniqid('os_' . $os_id . '_') . '.' . $ext;
                        $targetFile = $targetDir . $filename;

                        // Relative path for database
                        $dbPath = '/uploads/' . $year . '/' . $month . '/' . $filename;

                        if (move_uploaded_file($files['tmp_name'][$i], $targetFile)) {
                            $stmtPhoto->execute([$os_id, $dbPath]);
                        }
                    }
                }
            }

            $_SESSION['success'] = 'Ordem de Serviço criada com sucesso!';
            // Redirect to a list or generate PDF directly? Let's redirect to dashboard for now or maybe show a link to PDF
            // Let's redirect to list (Dashboard uses list) or maybe back to create?
            // User requested PDF generation method, let's redirect to a view that allows generation or just download it.
            // Usually redirection to list is better.
            // But for demo flow, let's redirect to a success page or the same form.
            // Actually, let's just go back to Dashboard.
            header('Location: ' . \BASE_URL . '/os');
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao salvar OS: ' . $e->getMessage();
            header('Location: ' . \BASE_URL . '/os/criar');
            exit;
        }
    }

    public function gerarPdf($id)
    {
        // Basic check if Dompdf exists
        if (!class_exists('Dompdf\Dompdf')) {
            die("Biblioteca Dompdf não encontrada. Instale via Composer.");
        }

        $db = Database::getInstance()->getConnection();

        // Fetch OS Data
        $stmt = $db->prepare("
            SELECT os.*, c.name as client_name, c.email as client_email, c.phone as client_phone, 
                   c.address as client_address, c.documento as client_doc, c.cnpj as client_cnpj,
                   u.name as tecnico_name
            FROM ordens_servico os
            JOIN clientes c ON os.client_id = c.id
            JOIN usuarios u ON os.user_id = u.id
            WHERE os.id = ?
        ");
        $stmt->execute([$id]);
        $os = $stmt->fetch();

        if (!$os) {
            die("OS não encontrada.");
        }

        // Fetch Photos
        $stmtPhotos = $db->prepare("SELECT * FROM os_fotos WHERE os_id = ?");
        $stmtPhotos->execute([$id]);
        $photos = $stmtPhotos->fetchAll();

        // Fetch Budget Items for "Peças" if linked
        $itens = [];
        if (!empty($os['orcamento_id'])) {
            $stmtItems = $db->prepare("SELECT * FROM orcamento_itens WHERE orcamento_id = ?");
            $stmtItems->execute([$os['orcamento_id']]);
            $itens = $stmtItems->fetchAll();
        }

        // Prepare Logo
        $logoPath = __DIR__ . '/../../public/assets/img/logo.jpg';
        if (file_exists($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $logoBase64 = 'data:image/jpeg;base64,' . $logoData;
        } else {
            $logoBase64 = '';
        }

        // Date String
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
        $timestamp = strtotime($os['created_at']);
        $day = date('d', $timestamp);
        $monthNum = (int) date('m', $timestamp);
        $year = date('Y', $timestamp);
        $dateExtenso = "$day de {$months[$monthNum]} de $year";

        // Generate HTML for PDF
        $html = '
        <html>
        <head>
            <style>
                @page { margin: 40px; }
                body { font-family: "Helvetica", sans-serif; font-size: 10pt; color: #000; line-height: 1.3; }
                
                .header { text-align: left; margin-bottom: 20px; border-bottom: 2px solid #eee; padding-bottom: 10px; }
                .logo-img { max-width: 250px; height: auto; }
                .header-details { font-size: 8pt; margin-top: 5px; }
                .header-details a { color: blue; text-decoration: underline; }
                
                .os-title { text-align: right; font-size: 14pt; font-weight: bold; margin-bottom: 5px; }
                .os-meta { text-align: right; font-size: 9pt; color: #555; margin-bottom: 20px; }
                
                .section-header { 
                    background-color: #f2f2f2; 
                    padding: 5px 10px; 
                    font-weight: bold; 
                    border-left: 4px solid #333; 
                    margin-top: 20px; 
                    margin-bottom: 10px; 
                    font-size: 11pt;
                }
                
                .content-block { margin-bottom: 10px; padding-left: 5px; }
                
                .table-items { width: 100%; border-collapse: collapse; margin-top: 5px; }
                .table-items th, .table-items td { border: 1px solid #ddd; padding: 6px; text-align: left; font-size: 9pt; }
                .table-items th { background-color: #f9f9f9; }
                
                .warning-box { 
                    border: 1px solid #ffcc00; 
                    background-color: #fffbe6; 
                    padding: 10px; 
                    font-size: 9pt; 
                    margin: 10px 0;
                    border-radius: 4px;
                }

                .photos { text-align: center; margin-top: 20px; }
                .photo-item { display: inline-block; margin: 10px; border: 1px solid #ddd; padding: 5px; }
                .photo-item img { max-width: 200px; max-height: 200px; }
                
                .footer { margin-top: 30px; font-size: 8pt; text-align: center; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
            </style>
        </head>
        <body>
            <!-- 1. Cabeçalho -->
            <div class="header">
                <table style="width: 100%;">
                    <tr>
                        <td valign="top">
                            <img src="' . $logoBase64 . '" class="logo-img" alt="ShopService Logo">
                            <div class="header-details">
                                MIK – SERVIÇOS HIDRAULICOS LTDA<br>
                                Av dos Imarés, 1383 – Indianópolis – São Paulo -SP<br>
                                Tel: (11) 5579-0835 / (11) 99376-4733<br>
                                <a href="http://shopservicevalvularedutora.com.br">shopservicevalvularedutora.com.br</a>
                            </div>
                        </td>
                        <td valign="top" style="text-align: right;">
                            <div class="os-title">Ordem de Serviço #' . $os['id'] . '</div>
                            <div class="os-meta">
                                Emissão: ' . date('d/m/Y', strtotime($os['created_at'])) . '<br>
                                Status: ' . strtoupper($os['status']) . '
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- 2. Dados do Cliente -->
            <div class="section-header">DADOS DO CLIENTE</div>
            <div class="content-block">
                <strong>' . htmlspecialchars($os['client_name']) . '</strong><br>
                ' . (!empty($os['client_address']) ? htmlspecialchars($os['client_address']) . '<br>' : '') . '
                ' . (!empty($os['client_email']) ? 'Email: ' . htmlspecialchars($os['client_email']) . ' | ' : '') . '
                ' . (!empty($os['client_phone']) ? 'Tel: ' . htmlspecialchars($os['client_phone']) : '') . '
            </div>

            <!-- 3. Serviços (Relatório) -->
            <div class="section-header">RELATÓRIO DE SERVIÇOS</div>
            <div class="content-block">
                ' . nl2br(htmlspecialchars($os['relatorio'])) . '
            </div>

            <!-- 4. Aviso -->
            ' . (!empty($os['aviso']) ? '
            <div class="section-header">AVISO IMPORTANTE</div>
            <div class="warning-box">
                ' . nl2br(htmlspecialchars($os['aviso'])) . '
            </div>
            ' : '') . '

            <!-- 5. Peças (Itens) -->
            <div class="section-header">PEÇAS / MATERIAIS ENVOLVIDOS</div>
            <div class="content-block">
                ' . (empty($itens) ? '<em>Nenhuma peça listada especificamente ou inclusas no relatório acima.</em>' : '
                <table class="table-items">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th width="15%">Qtd</th>
                        </tr>
                    </thead>
                    <tbody>
                        ' . implode('', array_map(function ($item) {
            return '<tr>
                                <td>' . htmlspecialchars($item['description']) . '</td>
                                <td align="center">' . $item['quantity'] . '</td>
                            </tr>';
        }, $itens)) . '
                    </tbody>
                </table>') . '
            </div>

            <!-- 6. Fotos do Serviço -->
            <div class="section-header">REGISTRO FOTOGRÁFICO</div>
            <div class="photos">
            ' . (empty($photos) ? '<em>Nenhuma foto anexada a esta OS.</em>' : '');

        foreach ($photos as $photo) {
            $path = __DIR__ . '/../../public' . $photo['photo_path'];
            if (file_exists($path)) {
                $type = pathinfo($path, PATHINFO_EXTENSION);
                if (!$type)
                    $type = 'jpg';
                $data = file_get_contents($path);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                $html .= '<div class="photo-item"><img src="' . $base64 . '"></div>';
            }
        }

        $html .= '
            </div>
            
            <div class="footer">
                Relatório gerado automaticamente pelo sistema ShopService em ' . date('d/m/Y H:i') . '
            </div>
        </body>
        </html>';

        $dompdf = new Dompdf();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->set_option('isRemoteEnabled', true);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("OS_" . $os['id'] . ".pdf", ["Attachment" => false]);
    }

    public function update_status()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $os_id = $_POST['os_id'] ?? null;
            $new_status = $_POST['status'] ?? null;

            if ($os_id && $new_status) {
                $db = Database::getInstance()->getConnection();

                // Fetch current validity to calculate next maintenance date
                $stmtFetch = $db->prepare("SELECT validade_meses FROM ordens_servico WHERE id = ?");
                $stmtFetch->execute([$os_id]);
                $osData = $stmtFetch->fetch();

                $data_proxima_manutencao = null;
                if ($new_status === 'Concluído' && !empty($osData['validade_meses'])) {
                    $meses = (int) $osData['validade_meses'];
                    if ($meses > 0) {
                        $data_proxima_manutencao = date('Y-m-d', strtotime("+$meses months"));
                    }
                }

                $stmt = $db->prepare("UPDATE ordens_servico SET status = ?, data_proxima_manutencao = ? WHERE id = ?");
                $stmt->execute([$new_status, $data_proxima_manutencao, $os_id]);

                $_SESSION['success'] = "Status da OS #$os_id atualizado para '$new_status'.";
            } else {
                $_SESSION['error'] = "Dados inválidos para atualização de status.";
            }

            // Redirect back to referrer or OS list
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    public function edit($id)
    {
        $db = Database::getInstance()->getConnection();

        // Fetch OS
        $stmt = $db->prepare("SELECT * FROM ordens_servico WHERE id = ?");
        $stmt->execute([$id]);
        $os = $stmt->fetch();

        if (!$os) {
            $_SESSION['error'] = "OS não encontrada.";
            header('Location: ' . \BASE_URL . '/os');
            exit;
        }

        // Fetch Clients for dropdown
        $stmtClients = $db->query("SELECT * FROM clientes ORDER BY name ASC");
        $clientes = $stmtClients->fetchAll();

        // Fetch Photos
        $stmtPhotos = $db->prepare("SELECT * FROM os_fotos WHERE os_id = ?");
        $stmtPhotos->execute([$id]);
        $photos = $stmtPhotos->fetchAll();

        // Fetch Budgets (Orcamentos) for linking
        // Only show budgets for the same client? Or all? Usually same client.
        $stmtOrcamentos = $db->prepare("
            SELECT id, created_at, total, status 
            FROM orcamentos 
            WHERE client_id = ? 
            ORDER BY id DESC
        ");
        $stmtOrcamentos->execute([$os['client_id']]);
        $orcamentos = $stmtOrcamentos->fetchAll();

        $viewContent = __DIR__ . '/../../views/os/edit.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/os');
            exit;
        }

        $id = $_POST['id'] ?? null;
        $client_id = $_POST['client_id'] ?? null;
        $status = $_POST['status'] ?? null;
        $tipo = $_POST['tipo'] ?? 'Execução';
        $relatorio = $_POST['relatorio'] ?? '';
        $aviso = $_POST['aviso'] ?? '';
        $orcamento_id = !empty($_POST['orcamento_id']) ? $_POST['orcamento_id'] : null;

        if (!$id || !$client_id || !$status) {
            $_SESSION['error'] = "Campos obrigatórios faltando.";
            header('Location: ' . \BASE_URL . '/os/edit/' . $id);
            exit;
        }

        $validade_meses = !empty($_POST['validade_meses']) ? (int) $_POST['validade_meses'] : null;

        // Handle Next Maintenance Date
        $data_proxima_manutencao = !empty($_POST['data_proxima_manutencao']) ? $_POST['data_proxima_manutencao'] : null;
        // Format date if needed (PT-BR to EN-US)? Usually HTML5 date input sends YYYY-MM-DD.
        // If text input with d/m/Y, need conversion.
        if ($data_proxima_manutencao && strpos($data_proxima_manutencao, '/') !== false) {
            $d = \DateTime::createFromFormat('d/m/Y', $data_proxima_manutencao);
            $data_proxima_manutencao = $d ? $d->format('Y-m-d') : null;
        }

        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("UPDATE ordens_servico SET client_id = ?, status = ?, relatorio = ?, orcamento_id = ?, tipo = ?, validade_meses = ?, aviso = ?, data_proxima_manutencao = ? WHERE id = ?");
            $stmt->execute([$client_id, $status, $relatorio, $orcamento_id, $tipo, $validade_meses, $aviso, $data_proxima_manutencao, $id]);

            // Sync: If orcamento_id is set, update that orcamento to point back to this OS?
            // "Vice Versa" requirement.
            if ($orcamento_id) {
                $stmtSync = $db->prepare("UPDATE orcamentos SET os_id = ? WHERE id = ?");
                $stmtSync->execute([$id, $orcamento_id]);
            }

            // Handle New Photos
            if (isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
                $year = date('Y');
                $month = date('m');
                $targetDir = __DIR__ . '/../../public/uploads/' . $year . '/' . $month . '/';

                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $files = $_FILES['fotos'];
                $count = count($files['name']);
                $stmtPhoto = $db->prepare("INSERT INTO os_fotos (os_id, photo_path) VALUES (?, ?)");

                for ($i = 0; $i < $count; $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                        $filename = uniqid('os_' . $id . '_') . '.' . $ext;
                        $targetFile = $targetDir . $filename;
                        $dbPath = '/uploads/' . $year . '/' . $month . '/' . $filename;

                        if (move_uploaded_file($files['tmp_name'][$i], $targetFile)) {
                            $stmtPhoto->execute([$id, $dbPath]);
                        }
                    }
                }
            }

            $_SESSION['success'] = "Ordem de Serviço #$id atualizada.";
            header('Location: ' . \BASE_URL . '/os');
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = "Erro ao atualizar: " . $e->getMessage();
            header('Location: ' . \BASE_URL . '/os/edit/' . $id);
            exit;
        }
    }
}
