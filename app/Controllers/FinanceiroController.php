<?php

namespace App\Controllers;

use App\Core\Database;

class FinanceiroController
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

        $db = Database::getInstance()->getConnection();

        // Filters
        $filterTags = $_GET['tags'] ?? [];
        $filterOs = $_GET['os_id'] ?? '';
        $filterStart = $_GET['data_inicio'] ?? '';
        $filterEnd = $_GET['data_fim'] ?? '';
        $filterType = $_GET['tipo'] ?? '';

        // Default to Current Month if no dates provided
        if (empty($filterStart) && empty($filterEnd) && empty($filterTags) && empty($filterOs) && empty($filterType)) {
            $filterStart = date('Y-m-01');
            $filterEnd = date('Y-m-t');
        }

        // Build Base Where Clause
        $where = "WHERE 1=1";
        $params = [];

        if (!empty($filterOs)) {
            $where .= " AND f.os_id_vinculo = ?";
            $params[] = $filterOs;
        }

        if (!empty($filterType)) {
            $where .= " AND f.tipo = ?";
            $params[] = $filterType;
        }

        if (!empty($filterStart)) {
            $where .= " AND f.data_vencimento >= ?";
            $params[] = $filterStart;
        }

        if (!empty($filterEnd)) {
            $where .= " AND f.data_vencimento <= ?";
            $params[] = $filterEnd;
        }

        if (!empty($filterTags) && is_array($filterTags)) {
            $placeholders = str_repeat('?,', count($filterTags) - 1) . '?';
            $where .= " AND f.id IN (
                SELECT ft2.financeiro_id 
                FROM financeiro_tags ft2 
                WHERE ft2.tag_id IN ($placeholders)
            )";
            $params = array_merge($params, $filterTags);
        }

        // Calculate Stats based on Filter
        $sqlStats = "SELECT tipo, status, SUM(valor) as total FROM financeiro f $where GROUP BY tipo, status";
        $stmtStats = $db->prepare($sqlStats);
        $stmtStats->execute($params);
        $statsData = $stmtStats->fetchAll();

        $totalRecebido = 0;
        $totalPagar = 0;
        $totalReceita = 0;
        $totalDespesa = 0;

        foreach ($statsData as $s) {
            if ($s['tipo'] == 'receita') {
                $totalReceita += $s['total'];
                if ($s['status'] == 'pago')
                    $totalRecebido += $s['total'];
            } elseif ($s['tipo'] == 'despesa') {
                $totalDespesa += $s['total'];
                if ($s['status'] == 'pendente')
                    $totalPagar += $s['total'];
            }
        }

        $saldoResultante = $totalReceita - $totalDespesa;

        // Fetch List
        $sqlList = "
            SELECT f.*, 
                   GROUP_CONCAT(CONCAT(t.name, '|', t.color) SEPARATOR ',') as tags 
            FROM financeiro f
            LEFT JOIN financeiro_tags ft ON f.id = ft.financeiro_id
            LEFT JOIN tags t ON ft.tag_id = t.id
            $where
            GROUP BY f.id 
            ORDER BY f.data_vencimento DESC 
            LIMIT 100
        ";

        $stmtList = $db->prepare($sqlList);
        $stmtList->execute($params);
        $lancamentos = $stmtList->fetchAll();

        // Fetch OSs for dropdown
        $stmtOS = $db->query("SELECT id, client_id FROM ordens_servico ORDER BY id DESC LIMIT 50");
        $osList = $stmtOS->fetchAll();

        // Fetch All Tags for Autocomplete/Dropdown could be useful, but let's stick to text search for now
        // Or fetching top tags for a datalist
        // Fetch All Tags for Autocomplete/Drodown
        $stmtTags = $db->query("SELECT * FROM tags ORDER BY name ASC");
        $allTags = $stmtTags->fetchAll();

        $viewContent = __DIR__ . '/../../views/financeiro/index.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/financeiro');
            exit;
        }

        $descricao = $_POST['descricao'] ?? '';
        $valor = $_POST['valor'] ?? 0;
        $tipo = $_POST['tipo'] ?? 'receita';
        $vencimento = $_POST['data_vencimento'] ?? date('Y-m-d');
        $status = $_POST['status'] ?? 'pendente';
        $os_id = !empty($_POST['os_id']) ? $_POST['os_id'] : null;
        $tagsInput = $_POST['tags'] ?? '';

        $db = Database::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            $stmt = $db->prepare("INSERT INTO financeiro (descricao, valor, tipo, data_vencimento, status, os_id_vinculo) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$descricao, $valor, $tipo, $vencimento, $status, $os_id]);
            $financeiroId = $db->lastInsertId();

            // Handle Tags
            if (!empty($tagsInput) && is_array($tagsInput)) {
                $stmtLink = $db->prepare("INSERT INTO financeiro_tags (financeiro_id, tag_id) VALUES (?, ?)");
                foreach ($tagsInput as $tagId) {
                    $stmtLink->execute([$financeiroId, $tagId]);
                }
            }

            // If it's a Revenue linked to OS and marked as Paid, update OS
            if ($os_id && $tipo === 'receita' && $status === 'pago') {
                $stmtOS = $db->prepare("UPDATE ordens_servico SET payment_status = 'Pago' WHERE id = ?");
                $stmtOS->execute([$os_id]);
            }

            $db->commit();
            $_SESSION['success'] = 'Lançamento salvo com sucesso!';
        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Erro ao salvar: ' . $e->getMessage();
        }

        header('Location: ' . \BASE_URL . '/financeiro');
        exit;
    }

    public function store_tag()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/financeiro');
            exit;
        }

        $tagName = trim($_POST['tag_name'] ?? '');
        $tagColor = $_POST['tag_color'] ?? '#6c757d';

        if (empty($tagName)) {
            $_SESSION['error'] = 'Nome da tag não pode ser vazio.';
        } else {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT IGNORE INTO tags (name, color) VALUES (?, ?)");
            $stmt->execute([$tagName, $tagColor]);
            $_SESSION['success'] = 'Tag adicionada com sucesso!';
        }

        header('Location: ' . \BASE_URL . '/financeiro');
        exit;
    }

    public function delete_tag()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/financeiro');
            exit;
        }

        $tagId = $_POST['tag_id'] ?? null;

        if ($tagId) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("DELETE FROM tags WHERE id = ?");

            try {
                $stmt->execute([$tagId]);
                $_SESSION['success'] = 'Tag removida com sucesso!';
            } catch (\Exception $e) {
                $_SESSION['error'] = 'Erro ao remover tag. Ela pode estar em uso.';
            }
        }

        header('Location: ' . \BASE_URL . '/financeiro');
        exit;
    }

    public function toggle_status()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/financeiro');
            exit;
        }

        $id = $_POST['id'] ?? null;
        if (!$id) {
            header('Location: ' . \BASE_URL . '/financeiro');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        try {
            $db->beginTransaction();

            // Get Current Status
            $stmt = $db->prepare("SELECT status, tipo, os_id_vinculo FROM financeiro WHERE id = ?");
            $stmt->execute([$id]);
            $item = $stmt->fetch();

            if ($item) {
                $newStatus = ($item['status'] === 'pago') ? 'pendente' : 'pago';

                $stmtUpdate = $db->prepare("UPDATE financeiro SET status = ? WHERE id = ?");
                $stmtUpdate->execute([$newStatus, $id]);

                // Update Linked OS if applicable
                // Logic: If Revenue becomes Paid -> OS Paid
                if ($item['tipo'] === 'receita' && $newStatus === 'pago' && !empty($item['os_id_vinculo'])) {
                    $stmtOS = $db->prepare("UPDATE ordens_servico SET payment_status = 'Pago' WHERE id = ?");
                    $stmtOS->execute([$item['os_id_vinculo']]);
                }

                $_SESSION['success'] = 'Status atualizado com sucesso!';
            }

            $db->commit();
        } catch (\Exception $e) {
            $db->rollBack();
            $_SESSION['error'] = 'Erro ao atualizar status.';
        }

        header('Location: ' . \BASE_URL . '/financeiro');
        exit;
    }
}
