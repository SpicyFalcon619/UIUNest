<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$responder_id = $_SESSION['user_id'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $post_id = $input['postId'] ?? null;
    $message = $input['message'] ?? '';
    
    if (!$post_id) {
        echo json_encode(['success' => false, 'error' => 'Missing post ID']);
        exit;
    }
    
    try {
        // 1. Verify user hasn't already responded
        $check = $pdo->prepare("SELECT response_id FROM seeking_responses WHERE post_id = ? AND responder_id = ?");
        $check->execute([$post_id, $responder_id]);
        if ($check->rowCount() > 0) {
            echo json_encode(['success' => false, 'error' => 'You have already responded to this post.']);
            exit;
        }

        $pdo->beginTransaction();

        // 2. Insert response
        $stmt = $pdo->prepare("INSERT INTO seeking_responses (post_id, responder_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $responder_id, $message]);
        
        // 3. Create Notification for the post creator
        $ownerStmt = $pdo->prepare("SELECT user_id FROM seeking_posts WHERE post_id = ?");
        $ownerStmt->execute([$post_id]);
        $post = $ownerStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($post && $post['user_id'] != $responder_id) {
            $notifMsg = $_SESSION['user']['name'] . " responded to your 'Looking for' post.";
            $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?, 'seeking_response', ?, ?)");
            $notifStmt->execute([$post['user_id'], $notifMsg, 'dashboard.html']);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Response submitted successfully.']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
}
?>
