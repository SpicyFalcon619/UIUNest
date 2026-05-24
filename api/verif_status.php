<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

try {
    $userId = $_SESSION['user']['id'];
    $stmt = $pdo->prepare("SELECT status FROM verifications WHERE user_id = ? ORDER BY submitted_at DESC LIMIT 1");
    $stmt->execute([$userId]);
    $verif = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($verif) {
        echo json_encode(['success' => true, 'status' => $verif['status']]);
    } else {
        echo json_encode(['success' => true, 'status' => 'none']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
