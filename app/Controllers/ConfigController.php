<?php

namespace App\Controllers;

use App\Core\Database;

class ConfigController
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

        // Get next ID for OS
        $stmtOS = $db->query("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'ordens_servico'");
        $nextIdOS = $stmtOS->fetchColumn();

        // Get next ID for Orcamentos
        $stmtOrc = $db->query("SELECT AUTO_INCREMENT FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orcamentos'");
        $nextIdOrc = $stmtOrc->fetchColumn();

        // If fetchColumn returns false (rare, maybe permissions), fallback to MAX + 1
        if (!$nextIdOS) {
            $stmtMax = $db->query("SELECT MAX(id) FROM ordens_servico");
            $nextIdOS = ($stmtMax->fetchColumn() ?: 0) + 1;
        }
        if (!$nextIdOrc) {
            $stmtMax = $db->query("SELECT MAX(id) FROM orcamentos");
            $nextIdOrc = ($stmtMax->fetchColumn() ?: 0) + 1;
        }

        $viewContent = __DIR__ . '/../../views/config/index.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/configuracoes');
            exit;
        }

        $newOsId = (int) ($_POST['os_start_id'] ?? 0);
        $newOrcId = (int) ($_POST['orcamento_start_id'] ?? 0);

        $db = Database::getInstance()->getConnection();
        $message = "";

        if ($newOsId > 0) {
            // Check current max to ensure we don't break things
            $stmtMax = $db->query("SELECT MAX(id) FROM ordens_servico");
            $currentMax = (int) $stmtMax->fetchColumn();

            if ($newOsId <= $currentMax) {
                $_SESSION['error'] = "O novo ID inicial para OS deve ser maior que o atual ({$currentMax}).";
            } else {
                try {
                    $db->exec("ALTER TABLE ordens_servico AUTO_INCREMENT = $newOsId");
                    $message .= "ID inicial de OS atualizado para $newOsId. ";
                } catch (\Exception $e) {
                    $_SESSION['error'] = "Erro ao atualizar OS ID: " . $e->getMessage();
                }
            }
        }

        if ($newOrcId > 0) {
            // Check current max
            $stmtMax = $db->query("SELECT MAX(id) FROM orcamentos");
            $currentMax = (int) $stmtMax->fetchColumn();

            if ($newOrcId <= $currentMax) {
                $_SESSION['error'] = (isset($_SESSION['error']) ? $_SESSION['error'] . " " : "") . "O novo ID inicial para Orçamentos deve ser maior que o atual ({$currentMax}).";
            } else {
                try {
                    $db->exec("ALTER TABLE orcamentos AUTO_INCREMENT = $newOrcId");
                    $message .= "ID inicial de Orçamentos atualizado para $newOrcId.";
                } catch (\Exception $e) {
                    $_SESSION['error'] = (isset($_SESSION['error']) ? $_SESSION['error'] . " " : "") . "Erro ao atualizar Orçamentos ID: " . $e->getMessage();
                }
            }
        }

        if ($message && !isset($_SESSION['error'])) {
            $_SESSION['success'] = $message;
        }

        header('Location: ' . \BASE_URL . '/configuracoes');
        exit;
    }
}
