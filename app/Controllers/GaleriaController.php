<?php

namespace App\Controllers;

use App\Core\Database;

class GaleriaController
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
            SELECT c.*, 
            (SELECT COUNT(*) FROM client_photos cp WHERE cp.client_id = c.id) as photo_count,
            (SELECT COUNT(*) FROM ordens_servico os JOIN os_fotos osf ON os.id = osf.os_id WHERE os.client_id = c.id) as os_photo_count
            FROM clientes c 
            ORDER BY c.name ASC
        ";

        // Search
        if (!empty($_GET['search'])) {
            $search = '%' . $_GET['search'] . '%';
            $sql = "
                SELECT c.*, 
                (SELECT COUNT(*) FROM client_photos cp WHERE cp.client_id = c.id) as photo_count,
                (SELECT COUNT(*) FROM ordens_servico os JOIN os_fotos osf ON os.id = osf.os_id WHERE os.client_id = c.id) as os_photo_count
                FROM clientes c 
                WHERE c.name LIKE ?
                ORDER BY c.name ASC
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute([$search]);
            $clientes = $stmt->fetchAll();
        } else {
            $stmt = $db->query($sql);
            $clientes = $stmt->fetchAll();
        }

        $viewContent = __DIR__ . '/../../views/galeria/index.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function show($id)
    {
        $db = Database::getInstance()->getConnection();

        // Client
        $stmt = $db->prepare("SELECT * FROM clientes WHERE id = ?");
        $stmt->execute([$id]);
        $client = $stmt->fetch();

        if (!$client) {
            header('Location: ' . \BASE_URL . '/galeria');
            exit;
        }

        // Installation Photos
        $stmtPhotos = $db->prepare("SELECT * FROM client_photos WHERE client_id = ? ORDER BY created_at DESC");
        $stmtPhotos->execute([$id]);
        $installationPhotos = $stmtPhotos->fetchAll();

        // Service History (OS with Photos)
        $stmtOS = $db->prepare("
            SELECT os.*, 
            (SELECT COUNT(*) FROM os_fotos WHERE os_fotos.os_id = os.id) as count
            FROM ordens_servico os
            WHERE os.client_id = ? AND 
            (SELECT COUNT(*) FROM os_fotos WHERE os_fotos.os_id = os.id) > 0
            ORDER BY os.created_at DESC
        ");
        $stmtOS->execute([$id]);
        $osList = $stmtOS->fetchAll();

        // Determine active tab (default to 'installation')
        $activeTab = $_GET['tab'] ?? 'installation';

        $viewContent = __DIR__ . '/../../views/galeria/show.php';
        require __DIR__ . '/../../views/layouts/layout.php';
    }

    public function upload()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $client_id = $_POST['client_id'] ?? null;
            $description = $_POST['description'] ?? '';

            if ($client_id && isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
                $db = Database::getInstance()->getConnection();

                $year = date('Y');
                $month = date('m');
                $targetDir = __DIR__ . '/../../public/uploads/clients/' . $client_id . '/';

                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $files = $_FILES['fotos'];
                $count = count($files['name']);
                $stmtPhoto = $db->prepare("INSERT INTO client_photos (client_id, photo_path, description) VALUES (?, ?, ?)");
                $successCount = 0;

                for ($i = 0; $i < $count; $i++) {
                    if ($files['error'][$i] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);
                        $filename = uniqid('client_' . $client_id . '_') . '.' . $ext;
                        $targetFile = $targetDir . $filename;
                        $dbPath = '/uploads/clients/' . $client_id . '/' . $filename;

                        if (move_uploaded_file($files['tmp_name'][$i], $targetFile)) {
                            $stmtPhoto->execute([$client_id, $dbPath, $description]);
                            $successCount++;
                        }
                    }
                }

                if ($successCount > 0) {
                    $_SESSION['success'] = "$successCount foto(s) de instalação adicionada(s).";
                } else {
                    $_SESSION['error'] = "Falha ao fazer upload das fotos.";
                }
            }

            header("Location: " . \BASE_URL . "/galeria/show/$client_id");
            exit;
        }
    }

    public function delete($id)
    {
        $db = Database::getInstance()->getConnection();

        // Fetch to get path
        $stmt = $db->prepare("SELECT * FROM client_photos WHERE id = ?");
        $stmt->execute([$id]);
        $photo = $stmt->fetch();

        if ($photo) {
            $stmtDel = $db->prepare("DELETE FROM client_photos WHERE id = ?");
            $stmtDel->execute([$id]);

            // Try to delete file
            $filePath = __DIR__ . '/../../public' . $photo['photo_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            $_SESSION['success'] = "Foto removida.";
            header("Location: " . \BASE_URL . "/galeria/show/" . $photo['client_id']);
            exit;
        }

        header("Location: " . \BASE_URL . "/galeria");
    }
}
