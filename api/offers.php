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
} elseif ($method === 'GET') {
    $item_id = $_GET['item_id'] ?? null;
    if (!$item_id) {
        echo json_encode(['success' => false, 'error' => 'Missing item ID']);
        exit;
    }
    try {
        $stmt = $pdo->prepare("
            SELECT o.*, u.name as buyer_name, u.email as buyer_email 
            FROM offers o 
            JOIN users u ON o.buyer_id = u.user_id 
            WHERE o.item_id = ? 
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$item_id]);
        $offers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'offers' => $offers]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $offer_id = $input['id'] ?? null;
    $status = $input['status'] ?? null;
    $counter_price = $input['counterPrice'] ?? null;

    if (!$offer_id || !$status) {
        echo json_encode(['success' => false, 'error' => 'Missing id or status']);
        exit;
    }

    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("
            SELECT o.*, i.seller_id, i.title, o.buyer_id 
            FROM offers o 
            JOIN items i ON o.item_id = i.item_id 
            WHERE o.offer_id = ?
        ");
        $stmt->execute([$offer_id]);
        $offer = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$offer || ($offer['seller_id'] != $buyer_id && $offer['buyer_id'] != $buyer_id)) {
            echo json_encode(['success' => false, 'error' => 'Unauthorized']);
            exit;
        }

        if ($status === 'countered') {
            $upd = $pdo->prepare("UPDATE offers SET status = ?, counter_price = ?, message = 'Seller countered the offer' WHERE offer_id = ?");
            $upd->execute([$status, $counter_price, $offer_id]);
        } else {
            $upd = $pdo->prepare("UPDATE offers SET status = ? WHERE offer_id = ?");
            $upd->execute([$status, $offer_id]);
        }

        // Send notification to the other party
        $notify_user = ($offer['seller_id'] == $buyer_id) ? $offer['buyer_id'] : $offer['seller_id'];
        $notifMsg = "Your offer on '" . $offer['title'] . "' was " . $status . ".";
        if ($status === 'accepted') {
            $notifMsg = "Offer on '" . $offer['title'] . "' was ACCEPTED! Check your dashboard.";
        } else if ($status === 'countered') {
            $notifMsg = "Seller countered your offer on '" . $offer['title'] . "' with ৳" . number_format($counter_price) . ".";
        }
        
        $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?, 'offer_update', ?, 'dashboard.html?tab=offers')");
        $notifStmt->execute([$notify_user, $notifMsg]);

        // If accepted, we could also update item status to 'sold' optionally, but let's just update offer status for now
        if ($status === 'accepted') {
            $itemUpd = $pdo->prepare("UPDATE items SET status = 'sold' WHERE item_id = ?");
            $itemUpd->execute([$offer['item_id']]);
        }

        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
?>
