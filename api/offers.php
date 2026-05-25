<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$buyer_id = $_SESSION['user_id'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $item_id = $input['itemId'] ?? null;
    $offer_price = $input['offerPrice'] ?? null;
    $message = $input['message'] ?? '';
    
    if (!$item_id || !$offer_price) {
        echo json_encode(['success' => false, 'error' => 'Missing item ID or offer price']);
        exit;
    }
    
    try {
        // 1. Verify user hasn't already offered
        $check = $pdo->prepare("SELECT offer_id FROM offers WHERE item_id = ? AND buyer_id = ?");
        $check->execute([$item_id, $buyer_id]);
        if ($check->rowCount() > 0) {
            echo json_encode(['success' => false, 'error' => 'You already have an active offer on this item.']);
            exit;
        }

        $pdo->beginTransaction();

        // 2. Insert offer
        $stmt = $pdo->prepare("INSERT INTO offers (item_id, buyer_id, offer_price, message) VALUES (?, ?, ?, ?)");
        $stmt->execute([$item_id, $buyer_id, $offer_price, $message]);
        
        // 3. Create Notification for the item seller
        $ownerStmt = $pdo->prepare("SELECT seller_id, title FROM items WHERE item_id = ?");
        $ownerStmt->execute([$item_id]);
        $item = $ownerStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($item && $item['seller_id'] != $buyer_id) {
            $notifMsg = $_SESSION['user']['name'] . " offered ৳" . number_format($offer_price) . " on your item: " . $item['title'];
            $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?, 'offer', ?, ?)");
            $notifStmt->execute([$item['seller_id'], $notifMsg, 'dashboard.html']);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Offer submitted successfully.']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
}
?>
