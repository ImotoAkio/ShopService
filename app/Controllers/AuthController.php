<?php

namespace App\Controllers;

use App\Core\Database;
use PDO;

class AuthController
{

    public function login()
    {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . \BASE_URL . '/dashboard');
            exit;
        }
        require __DIR__ . '/../../views/auth/login.php';
    }

    public function authenticate()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/login');
            exit;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT id, name, password FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            header('Location: ' . \BASE_URL . '/dashboard');
            exit;
        } else {
            $_SESSION['error'] = 'Invalid credentials';
            header('Location: ' . \BASE_URL . '/login');
            exit;
        }
    }

    public function logout()
    {
        session_destroy();
        header('Location: ' . \BASE_URL . '/login');
        exit;
    }

    public function register_admin()
    {
        require __DIR__ . '/../../views/auth/register_admin.php';
    }

    public function store_admin()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . \BASE_URL . '/admin/register');
            exit;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if ($password !== $password_confirm) {
            $_SESSION['error'] = 'As senhas não coincidem.';
            header('Location: ' . \BASE_URL . '/admin/register');
            exit;
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO usuarios (name, email, password, role) VALUES (:name, :email, :password, 'admin')");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':password' => $hashed_password
            ]);

            header('Location: ' . \BASE_URL . '/login');
            exit;

        } catch (\PDOException $e) {
            $_SESSION['error'] = 'Erro ao cadastrar: ' . $e->getMessage();
            header('Location: ' . \BASE_URL . '/admin/register');
            exit;
        }
    }
}
