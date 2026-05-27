<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user'])) {
        echo json_encode(['success' => false, 'error' => 'Not authenticated']);
        exit;
    }
    
    $input = json_decode(file_get_contents('php://input'), true);
    $userId = $_SESSION['user']['id'];
    
    $listingId = $input['listingId'] ?? null;
    $val = $input['money'] ?? 3;
    $acc = $input['accuracy'] ?? 3;
    $resp = $input['response'] ?? 3;
    $clean = $input['cleanliness'] ?? 3;
    $safe = $input['safety'] ?? 3;
    $comment = $input['comment'] ?? '';
    
    if (!$listingId) {
        echo json_encode(['success' => false, 'error' => 'Missing listing ID']);
        exit;
    }
    
    $composite = round(($val + $acc + $resp + $clean + $safe) / 5, 1);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO reviews (listing_id, reviewer_id, value_for_money, listing_accuracy, landlord_response, cleanliness, safety, composite_score, comment) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $listingId, $userId, $val, $acc, $resp, $clean, $safe, $composite, $comment
        ]);
        
        echo json_encode(['success' => true]);
    } catch(Exception $e) {
        if ($e->getCode() == 23000) { // duplicate entry for unique constraint
            echo json_encode(['success' => false, 'error' => 'You have already reviewed this listing.']);
        } else {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
?>
