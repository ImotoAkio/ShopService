<?php

namespace App\Controllers;

use App\Core\Database;

class DashboardController
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

        // Stats Counts
        $stats = [];

        $stmt = $db->query("SELECT COUNT(*) FROM clientes");
        $stats['total_clientes'] = $stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(*) FROM ordens_servico WHERE status != 'Concluído'"); // Assuming 'Concluído' closes it
        $stats['open_os'] = $stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(*) FROM orcamentos WHERE status = 'Pendente'");
        $stats['pending_quotes'] = $stmt->fetchColumn();

        $stmt = $db->query("SELECT COUNT(*) FROM ativos");
        $stats['total_assets'] = $stmt->fetchColumn();

        // Recent Activity (Last 5 OS)
        $stmt = $db->query("
            SELECT os.*, c.name as client_name 
            FROM ordens_servico os 
            JOIN clientes c ON os.client_id = c.id 
            ORDER BY os.created_at DESC 
            LIMIT 5
        ");
        $recent_os = $stmt->fetchAll();

        // Expired Maintenance (Maintenance Due)
        // Fetch OS where data_proxima_manutencao has passed or is today, and status is "Concluído" (it should be, but just in case)
        $stmtAlerts = $db->query("
            SELECT os.*, c.name as client_name 
            FROM ordens_servico os 
            JOIN clientes c ON os.client_id = c.id 
            WHERE os.data_proxima_manutencao IS NOT NULL 
            AND os.data_proxima_manutencao <= CURDATE()
            ORDER BY os.data_proxima_manutencao ASC
        ");
        $maintenance_alerts = $stmtAlerts->fetchAll();

        // Sales Chart Data (Last 6 Months Revenue - simplified)
        // For now, static or simple query if Financeiro exists
        // Let's stick to simple stats for now as requested.

        $viewContent = __DIR__ . '/../../views/dashboard/index.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }
}
