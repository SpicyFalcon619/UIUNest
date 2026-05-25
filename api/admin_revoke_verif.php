<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $input['user_id'] ?? 0;

    if (!$userId) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Update verification status to revoked
        $stmt = $pdo->prepare("UPDATE verifications SET status = 'revoked' WHERE user_id = ? AND status = 'approved'");
        $stmt->execute([$userId]);

        // Unverify all their listings
        $stmt = $pdo->prepare("UPDATE listings SET is_verified = 0 WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Notify the user
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message, link, is_read) VALUES (?, 'system', 'Your account verification has been revoked by an admin. Your listings are now marked as unverified and you must re-apply.', 'profile.html', 0)");
        $notifStmt->execute([$userId]);

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}
?>
