<?php

namespace App\Controllers;

use App\Core\Database;

class ClienteController
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

        // Parameters
        $search = $_GET['search'] ?? '';
        $orderBy = $_GET['order_by'] ?? 'name';
        $direction = $_GET['direction'] ?? 'ASC';

        // Valid columns for ordering to prevent SQL Injection
        $allowedColumns = ['id', 'name', 'email', 'phone', 'documento'];
        if (!in_array($orderBy, $allowedColumns)) {
            $orderBy = 'name';
        }
        if (!in_array(strtoupper($direction), ['ASC', 'DESC'])) {
            $direction = 'ASC';
        }

        // Build Query
        $sql = "SELECT * FROM clientes WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (name LIKE ? OR email LIKE ? OR documento LIKE ? OR phone LIKE ?)";
            $searchTerm = '%' . $search . '%';
            $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm];
        }

        $sql .= " ORDER BY $orderBy $direction";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $clientes = $stmt->fetchAll();

        // Pass filter vars to view
        $filter = [
            'search' => $search,
            'order_by' => $orderBy,
            'direction' => $direction
        ];

        $viewContent = __DIR__ . '/../../views/clientes/index.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function create()
    {
        $viewContent = __DIR__ . '/../../views/clientes/create.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/clientes');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $documento = $_POST['documento'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';

        if (empty($name)) {
            $_SESSION['error'] = 'Nome é obrigatório.';
            header('Location: ' . \BASE_URL . '/clientes/criar');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("INSERT INTO clientes (
                name, documento, email, phone, address, responsavel, cargo, telefone2, cnpj,
                zelador_nome, zelador_tel, zelador_tel2, zelador_email,
                sindico_nome, sindico_tel, sindico_tel2, sindico_email,
                admin_nome, admin_tel, admin_tel2, admin_email
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?
            )");

            $stmt->execute([
                $name,
                $documento,
                $email,
                $phone,
                $address,
                $_POST['responsavel'] ?? '',
                $_POST['cargo'] ?? '',
                $_POST['telefone2'] ?? '',
                $_POST['cnpj'] ?? '',
                $_POST['zelador_nome'] ?? '',
                $_POST['zelador_tel'] ?? '',
                $_POST['zelador_tel2'] ?? '',
                $_POST['zelador_email'] ?? '',
                $_POST['sindico_nome'] ?? '',
                $_POST['sindico_tel'] ?? '',
                $_POST['sindico_tel2'] ?? '',
                $_POST['sindico_email'] ?? '',
                $_POST['admin_nome'] ?? '',
                $_POST['admin_tel'] ?? '',
                $_POST['admin_tel2'] ?? '',
                $_POST['admin_email'] ?? ''
            ]);

            $_SESSION['success'] = 'Cliente cadastrado com sucesso!';
            header('Location: ' . \BASE_URL . '/clientes');
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao cadastrar cliente: ' . $e->getMessage();
            header('Location: ' . \BASE_URL . '/clientes/criar');
            exit;
        }
    }

    public function view($id)
    {
        $db = Database::getInstance()->getConnection();

        // Fetch Client
        $stmt = $db->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        $cliente = $stmt->fetch();

        if (!$cliente) {
            echo "Cliente não encontrado.";
            return;
        }

        // Fetch History: Service Orders
        $stmtOS = $db->prepare("SELECT * FROM ordens_servico WHERE client_id = ? ORDER BY created_at DESC");
        $stmtOS->execute([$id]);
        $historicoOS = $stmtOS->fetchAll();

        // Fetch History: Quotes
        $stmtOrc = $db->prepare("SELECT * FROM orcamentos WHERE client_id = ? ORDER BY created_at DESC");
        $stmtOrc->execute([$id]);
        $historicoOrc = $stmtOrc->fetchAll();

        // Fetch Assets
        $stmtAtivos = $db->prepare("SELECT * FROM ativos WHERE client_id = ? ORDER BY created_at DESC");
        $stmtAtivos->execute([$id]);
        $ativos = $stmtAtivos->fetchAll();

        $viewContent = __DIR__ . '/../../views/clientes/view.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }
}
