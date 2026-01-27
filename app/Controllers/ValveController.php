<?php

namespace App\Controllers;

use App\Core\Database;

class ValveController
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
        $stmt = $db->query("SELECT * FROM valve_models ORDER BY name ASC");
        $valves = $stmt->fetchAll();

        $viewContent = __DIR__ . '/../../views/valves/index.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function create()
    {
        $viewContent = __DIR__ . '/../../views/valves/create.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/valves/create');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $service_description = $_POST['service_description'] ?? '';
        $procedures = $_POST['procedures'] ?? '';
        $observations = $_POST['observations'] ?? '';

        if (empty($name)) {
            $_SESSION['error'] = 'Nome do modelo é obrigatório.';
            header('Location: ' . \BASE_URL . '/valves/create');
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO valve_models (name, service_description, procedures, observations) VALUES (?, ?, ?, ?)");

        if ($stmt->execute([$name, $service_description, $procedures, $observations])) {
            $_SESSION['success'] = 'Modelo VRP criado com sucesso!';
            header('Location: ' . \BASE_URL . '/valves');
        } else {
            $_SESSION['error'] = 'Erro ao criar modelo.';
            header('Location: ' . \BASE_URL . '/valves/create');
        }
        exit;
    }

    public function edit($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM valve_models WHERE id = ?");
        $stmt->execute([$id]);
        $valve = $stmt->fetch();

        if (!$valve) {
            $_SESSION['error'] = 'Modelo não encontrado.';
            header('Location: ' . \BASE_URL . '/valves');
            exit;
        }

        $viewContent = __DIR__ . '/../../views/valves/edit.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/valves');
            exit;
        }

        $id = $_POST['id'];
        $name = $_POST['name'] ?? '';
        $service_description = $_POST['service_description'] ?? '';
        $procedures = $_POST['procedures'] ?? '';
        $observations = $_POST['observations'] ?? '';

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE valve_models SET name = ?, service_description = ?, procedures = ?, observations = ? WHERE id = ?");

        if ($stmt->execute([$name, $service_description, $procedures, $observations, $id])) {
            $_SESSION['success'] = 'Modelo atualizado com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao atualizar modelo.';
        }

        header('Location: ' . \BASE_URL . '/valves');
        exit;
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/valves');
            exit;
        }

        $id = $_POST['id'];
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM valve_models WHERE id = ?");

        if ($stmt->execute([$id])) {
            $_SESSION['success'] = 'Modelo excluído com sucesso!';
        } else {
            $_SESSION['error'] = 'Erro ao excluir modelo.';
        }

        header('Location: ' . \BASE_URL . '/valves');
        exit;
    }
}
