<?php

namespace App\Controllers;

use App\Core\Database;

class PhotoController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            exit;
        }
    }

    public function updateDetails()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        $type = $input['type'] ?? null; // 'os' or 'client'
        $description = $input['description'] ?? '';
        $observacoes = $input['observacoes'] ?? '';

        if (!$id || !$type) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $table = ($type === 'os') ? 'os_fotos' : 'client_photos';

        try {
            $stmt = $db->prepare("UPDATE $table SET description = ?, observacoes = ? WHERE id = ?");
            $stmt->execute([$description, $observacoes, $id]);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function saveImage()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? null;
        $type = $input['type'] ?? null;
        $imageData = $input['image'] ?? null; // Base64

        if (!$id || !$type || !$imageData) {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
            exit;
        }

        $db = Database::getInstance()->getConnection();
        $table = ($type === 'os') ? 'os_fotos' : 'client_photos';

        // Get current path
        $stmt = $db->prepare("SELECT photo_path FROM $table WHERE id = ?");
        $stmt->execute([$id]);
        $photo = $stmt->fetch();

        if (!$photo) {
            echo json_encode(['success' => false, 'message' => 'Photo not found']);
            exit;
        }

        // Decode Image
        $data = explode(',', $imageData);
        $base64 = $data[1] ?? $data[0];
        $binary = base64_decode($base64);

        if (!$binary) {
            echo json_encode(['success' => false, 'message' => 'Failed to decode image']);
            exit;
        }

        $filePath = __DIR__ . '/../../public' . $photo['photo_path'];

        // Save (Overwrite)
        if (file_put_contents($filePath, $binary)) {
            // Update timestamp maybe? Not necessary for functionality but good for cache busting.
            // Actually, browser cache might be an issue. Ideally we should version the file or use a cache buster query param in view.
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to write file']);
        }
    }
}
