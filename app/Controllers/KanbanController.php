<?php

namespace App\Controllers;

use App\Core\Database;

class KanbanController
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

        // Fetch all OS, ordered by date
        $sql = "
            SELECT os.*, c.name as client_name 
            FROM ordens_servico os 
            JOIN clientes c ON os.client_id = c.id 
            ORDER BY os.created_at DESC
        ";
        $stmt = $db->query($sql);
        $ordens = $stmt->fetchAll();

        // Group by Status
        $kanbanData = [
            'Aberto' => [],
            'Em Andamento' => [],
            'Aguardando Peças' => [],
            'Concluído' => []
        ];

        foreach ($ordens as $os) {
            $status = $os['status'];
            // Normalize status if needed or handle unknown statuses
            if (!array_key_exists($status, $kanbanData)) {
                $kanbanData['Aberto'][] = $os; // Fallback or maybe create a generic 'Outros'?
            } else {
                $kanbanData[$status][] = $os;
            }
        }

        $viewContent = __DIR__ . '/../../views/kanban/index.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function move()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $osId = $input['os_id'] ?? null;
        $newStatus = $input['status'] ?? null;

        if (!$osId || !$newStatus) {
            echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
            exit;
        }

        $db = Database::getInstance()->getConnection();

        try {
            // Update status
            $stmt = $db->prepare("UPDATE ordens_servico SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $osId]);

            // If concluded, maybe set maintenance date? Handled in OSController, duplication of logic?
            // For now, simple status update. Ideally, we should refactor shared logic to a Service class.
            // I'll add the maintenance date logic here too for consistency if it's 'Concluído'.
            if ($newStatus === 'Concluído') {
                $stmtFetch = $db->prepare("SELECT validade_meses FROM ordens_servico WHERE id = ?");
                $stmtFetch->execute([$osId]);
                $osData = $stmtFetch->fetch();

                if (!empty($osData['validade_meses']) && (int) $osData['validade_meses'] > 0) {
                    $meses = (int) $osData['validade_meses'];
                    $data_proxima_manutencao = date('Y-m-d', strtotime("+$meses months"));
                    $stmtUpdateDate = $db->prepare("UPDATE ordens_servico SET data_proxima_manutencao = ? WHERE id = ?");
                    $stmtUpdateDate->execute([$data_proxima_manutencao, $osId]);
                }
            }

            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
