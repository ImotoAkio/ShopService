<?php

namespace App\Controllers;

use App\Core\Database;
use PDO;

class AtivoController
{

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function requireAuth()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . \BASE_URL . '/login');
            exit;
        }
    }

    public function index()
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        // Join with Clientes to get owner name
        $stmt = $db->query("
            SELECT a.*, c.name as client_name 
            FROM ativos a 
            LEFT JOIN clientes c ON a.client_id = c.id 
            ORDER BY a.created_at DESC
        ");
        $ativos = $stmt->fetchAll();

        $viewContent = __DIR__ . '/../../views/ativos/index.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function create()
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT id, name FROM clientes ORDER BY name ASC");
        $clientes = $stmt->fetchAll();

        $viewContent = __DIR__ . '/../../views/ativos/create.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function store()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $client_id = !empty($_POST['client_id']) ? $_POST['client_id'] : null;
            $uuid = $this->generateUUID();

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO ativos (uuid, name, description, client_id) VALUES (:uuid, :name, :description, :client_id)");

            try {
                $stmt->execute([
                    'uuid' => $uuid,
                    'name' => $name,
                    'description' => $description,
                    'client_id' => $client_id
                ]);
                $_SESSION['success'] = "Ativo criado com sucesso!";
                header('Location: ' . \BASE_URL . '/ativos');
                exit;
            } catch (\Exception $e) {
                $_SESSION['error'] = "Erro ao criar ativo: " . $e->getMessage();
                header('Location: ' . \BASE_URL . '/ativos/criar');
                exit;
            }
        }
    }

    public function details($id)
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        // Fetch Asset
        $stmt = $db->prepare("
            SELECT a.*, c.name as client_name 
            FROM ativos a 
            LEFT JOIN clientes c ON a.client_id = c.id 
            WHERE a.id = :id
        ");
        $stmt->execute(['id' => $id]);
        $ativo = $stmt->fetch();

        if (!$ativo) {
            $_SESSION['error'] = "Ativo não encontrado.";
            header('Location: ' . \BASE_URL . '/ativos');
            exit;
        }

        // Fetch History (OS)
        $stmtHistory = $db->prepare("
            SELECT os.*, c.name as client_name
            FROM ordens_servico os
            LEFT JOIN clientes c ON os.client_id = c.id
            WHERE os.ativo_id = ?
            ORDER BY os.created_at DESC
        ");
        $stmtHistory->execute([$ativo['id']]);
        $historico = $stmtHistory->fetchAll();

        $viewContent = __DIR__ . '/../../views/ativos/internal_view.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function view($uuid)
    {
        // Public Access - No checkAuth() here
        $db = Database::getInstance()->getConnection();

        // Fetch Asset with Client Name
        $stmt = $db->prepare("
            SELECT a.*, c.name as client_name 
            FROM ativos a 
            LEFT JOIN clientes c ON a.client_id = c.id 
            WHERE a.uuid = :uuid
        ");
        $stmt->execute(['uuid' => $uuid]);
        $ativo = $stmt->fetch();

        if (!$ativo) {
            http_response_code(404);
            echo "Ativo não encontrado.";
            return;
        }

        // Fetch History (OS)
        $stmtHistory = $db->prepare("
            SELECT os.*, c.name as client_name
            FROM ordens_servico os
            LEFT JOIN clientes c ON os.client_id = c.id
            WHERE os.ativo_id = ?
            ORDER BY os.created_at DESC
        ");
        $stmtHistory->execute([$ativo['id']]);
        $historico = $stmtHistory->fetchAll();

        // Use a dedicated public view
        require __DIR__ . '/../../views/ativos/public_view.php';
    }

    public function edit($id)
    {
        $this->requireAuth();
        $db = Database::getInstance()->getConnection();

        // Fetch Asset
        $stmt = $db->prepare("SELECT * FROM ativos WHERE id = ?");
        $stmt->execute([$id]);
        $ativo = $stmt->fetch();

        if (!$ativo) {
            $_SESSION['error'] = "Ativo não encontrado.";
            header('Location: ' . \BASE_URL . '/ativos');
            exit;
        }

        // Fetch Clients for dropdown
        $stmtClientes = $db->query("SELECT id, name FROM clientes ORDER BY name ASC");
        $clientes = $stmtClientes->fetchAll();

        // View
        $viewContent = __DIR__ . '/../../views/ativos/edit.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function update()
    {
        $this->requireAuth();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $client_id = !empty($_POST['client_id']) ? $_POST['client_id'] : null;

            if (!$id) {
                $_SESSION['error'] = "ID do ativo inválido.";
                header('Location: ' . \BASE_URL . '/ativos');
                exit;
            }

            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("UPDATE ativos SET name = ?, description = ?, client_id = ? WHERE id = ?");

            try {
                $stmt->execute([$name, $description, $client_id, $id]);
                $_SESSION['success'] = "Ativo atualizado com sucesso!";
            } catch (\Exception $e) {
                $_SESSION['error'] = "Erro ao atualizar ativo: " . $e->getMessage();
            }

            header('Location: ' . \BASE_URL . '/ativos');
            exit;
        }
    }

    public function delete()
    {
        $this->requireAuth();
        $id = $_POST['id'] ?? $_GET['id'] ?? null; // Support both for now, but prefer POST

        if (!$id) {
            $_SESSION['error'] = "ID inválido.";
            header('Location: ' . \BASE_URL . '/ativos');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM ativos WHERE id = ?");

        try {
            $stmt->execute([$id]);
            $_SESSION['success'] = "Ativo excluído com sucesso!";
        } catch (\Exception $e) {
            $_SESSION['error'] = "Erro ao excluir: " . $e->getMessage();
        }

        header('Location: ' . \BASE_URL . '/ativos');
        exit;
    }

    private function generateUUID()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}
