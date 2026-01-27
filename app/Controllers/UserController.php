<?php

namespace App\Controllers;

use App\Core\Database;

class UserController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Only allow logged in users. Ideally only Admins, but for now just authenticated.
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . \BASE_URL . '/login');
            exit;
        }
    }

    public function index()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query("SELECT * FROM usuarios ORDER BY name ASC");
        $users = $stmt->fetchAll();

        $viewContent = __DIR__ . '/../../views/usuarios/index.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function create()
    {
        // Check if user is admin (optional, assuming 'role' in session or DB)
        // For simplicity, allowed for now.
        $viewContent = __DIR__ . '/../../views/usuarios/create.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/usuarios');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'employee';

        if (empty($name) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios.';
            header('Location: ' . \BASE_URL . '/usuarios/criar');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        try {
            // Check email
            $stmtCheck = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmtCheck->execute([$email]);
            if ($stmtCheck->fetch()) {
                throw new \Exception("E-mail já cadastrado.");
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO usuarios (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $hashed_password, $role]);

            $_SESSION['success'] = 'Usuário criado com sucesso!';
            header('Location: ' . \BASE_URL . '/usuarios');
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao criar usuário: ' . $e->getMessage();
            header('Location: ' . \BASE_URL . '/usuarios/criar');
            exit;
        }
    }

    public function edit($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch();

        if (!$user) {
            $_SESSION['error'] = 'Usuário não encontrado.';
            header('Location: ' . \BASE_URL . '/usuarios');
            exit;
        }

        $viewContent = __DIR__ . '/../../views/usuarios/edit.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/usuarios');
            exit;
        }

        $id = $_POST['id'] ?? null;
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $role = $_POST['role'] ?? 'employee';
        $password = $_POST['password'] ?? '';

        if (!$id || empty($name) || empty($email)) {
            $_SESSION['error'] = 'Preencha todos os campos obrigatórios.';
            header('Location: ' . \BASE_URL . '/usuarios/edit/' . $id);
            exit;
        }

        $db = Database::getInstance()->getConnection();

        try {
            // Check email uniqueness (exclude current user)
            $stmtCheck = $db->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmtCheck->execute([$email, $id]);
            if ($stmtCheck->fetch()) {
                throw new \Exception("E-mail já utilizado por outro usuário.");
            }

            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("UPDATE usuarios SET name = ?, email = ?, role = ?, password = ? WHERE id = ?");
                $stmt->execute([$name, $email, $role, $hashed_password, $id]);
            } else {
                $stmt = $db->prepare("UPDATE usuarios SET name = ?, email = ?, role = ? WHERE id = ?");
                $stmt->execute([$name, $email, $role, $id]);
            }

            $_SESSION['success'] = 'Usuário atualizado com sucesso!';
            header('Location: ' . \BASE_URL . '/usuarios');
            exit;

        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao atualizar: ' . $e->getMessage();
            header('Location: ' . \BASE_URL . '/usuarios/edit/' . $id);
            exit;
        }
    }

    public function delete($id)
    {
        // Prevent deleting self
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'Você não pode excluir a si mesmo.';
            header('Location: ' . \BASE_URL . '/usuarios');
            exit;
        }

        $db = Database::getInstance()->getConnection();

        try {
            $stmt = $db->prepare("DELETE FROM usuarios WHERE id = ?");
            $stmt->execute([$id]);
            $_SESSION['success'] = 'Usuário excluído com sucesso.';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Erro ao excluir: ' . $e->getMessage();
        }

        header('Location: ' . \BASE_URL . '/usuarios');
        exit;
    }
}
