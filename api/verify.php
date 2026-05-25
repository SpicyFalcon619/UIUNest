<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $userId = $_SESSION['user']['id'];
    $nidType = $input['nidType'] ?? '';
    $desc = $input['desc'] ?? '';
    $docPath = $input['documentPath'] ?? '';

    if (empty($nidType) || empty($docPath)) {
        echo json_encode(['success' => false, 'error' => 'Document Type and Document are required.']);
        exit;
    }

    try {
        // Check if user already has a pending or approved verification
        $stmt = $pdo->prepare("SELECT * FROM verifications WHERE user_id = ? AND status IN ('pending', 'approved')");
        $stmt->execute([$userId]);
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => false, 'error' => 'Verification request already pending or approved.']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO verifications (user_id, nid_type, document_path, description, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$userId, $nidType, $docPath, $desc]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
