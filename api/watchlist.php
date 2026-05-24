<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    try {
        $stmt = $pdo->prepare("SELECT listing_id FROM watchlists WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $ids = array_map(function($r) { return (int)$r['listing_id']; }, $rows);
        
        echo json_encode(['success' => true, 'watchlist' => $ids]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $listing_id = $input['listingId'] ?? null;
    
    if (!$listing_id) {
        echo json_encode(['success' => false, 'error' => 'listingId required']);
        exit;
    }
    
    try {
        // Check if exists
        $chk = $pdo->prepare("SELECT watchlist_id FROM watchlists WHERE user_id = ? AND listing_id = ?");
        $chk->execute([$user_id, $listing_id]);
        $exists = $chk->fetch();
        
        if ($exists) {
            // Remove it
            $stmt = $pdo->prepare("DELETE FROM watchlists WHERE watchlist_id = ?");
            $stmt->execute([$exists['watchlist_id']]);
            echo json_encode(['success' => true, 'action' => 'removed']);
        } else {
            // Add it
            $stmt = $pdo->prepare("INSERT INTO watchlists (user_id, listing_id) VALUES (?, ?)");
            $stmt->execute([$user_id, $listing_id]);
            echo json_encode(['success' => true, 'action' => 'added']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Method not allowed']);
}
?>
