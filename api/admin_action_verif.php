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
    
    $verifId = $input['id'] ?? 0;
    $action = $input['action'] ?? '';

    if (!$verifId || !in_array($action, ['approve', 'reject'])) {
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        $status = ($action === 'approve') ? 'approved' : 'rejected';
        
        $stmt = $pdo->prepare("UPDATE verifications SET status = ? WHERE verification_id = ?");
        $stmt->execute([$status, $verifId]);

        if ($action === 'approve') {
            // Find the user_id for this verification
            $stmt = $pdo->prepare("SELECT user_id FROM verifications WHERE verification_id = ?");
            $stmt->execute([$verifId]);
            $verif = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($verif) {
                // Verify all listings owned by this user
                $stmt = $pdo->prepare("UPDATE listings SET is_verified = 1 WHERE user_id = ?");
                $stmt->execute([$verif['user_id']]);
                
                // Add notification
                $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message, link, is_read) VALUES (?, 'system', 'Congratulations! Your account verification has been approved. Your listings will now display a verified badge.', 'profile.html', 0)");
                $notifStmt->execute([$verif['user_id']]);
            }
        } else {
            // Rejected
            $stmt = $pdo->prepare("SELECT user_id FROM verifications WHERE verification_id = ?");
            $stmt->execute([$verifId]);
            $verif = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($verif) {
                // Add notification
                $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message, link, is_read) VALUES (?, 'system', 'Your account verification request was rejected. Please review our guidelines and try again.', 'profile.html', 0)");
                $notifStmt->execute([$verif['user_id']]);
            }
        }

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
