<?php
require 'db.php';
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE user_id = ? 
            ORDER BY created_at DESC 
            LIMIT 50
        ");
        $stmt->execute([$user_id]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $unread_count = 0;
        foreach ($notifications as $n) {
            if (!$n['is_read']) $unread_count++;
        }
        
        echo json_encode([
            'success' => true, 
            'notifications' => $notifications,
            'unread_count' => $unread_count
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $notif_id = $input['id'] ?? null;
    
    try {
        if ($notif_id === 'all') {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE user_id = ?");
            $stmt->execute([$user_id]);
        } else if ($notif_id) {
            $stmt = $pdo->prepare("UPDATE notifications SET is_read = TRUE WHERE notif_id = ? AND user_id = ?");
            $stmt->execute([$notif_id, $user_id]);
        }
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
}
?>
