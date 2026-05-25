<?php
require_once 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$type = $input['type'] ?? '';
$id = $input['id'] ?? 0;
$status = $input['status'] ?? '';
$userId = $_SESSION['user_id'];

try {
    if ($type === 'listing') {
        $stmt = $pdo->prepare("UPDATE listings SET status = ? WHERE listing_id = ? AND user_id = ?");
        $stmt->execute([$status, $id, $userId]);
    } elseif ($type === 'item') {
        $stmt = $pdo->prepare("UPDATE items SET status = ? WHERE item_id = ? AND seller_id = ?");
        $stmt->execute([$status, $id, $userId]);
    } elseif ($type === 'seeking') {
        $stmt = $pdo->prepare("UPDATE seeking_posts SET status = ? WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$status, $id, $userId]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid type']);
        exit;
    }
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Update failed or unauthorized']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
