<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$applicant_id = $_SESSION['user_id'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $listing_id = $input['listingId'] ?? null;
    $message = $input['message'] ?? '';
    
    if (!$listing_id) {
        echo json_encode(['success' => false, 'error' => 'Missing listing ID']);
        exit;
    }
    
    try {
        // 1. Verify user hasn't already applied
        $check = $pdo->prepare("SELECT application_id FROM applications WHERE listing_id = ? AND applicant_id = ?");
        $check->execute([$listing_id, $applicant_id]);
        if ($check->rowCount() > 0) {
            echo json_encode(['success' => false, 'error' => 'You have already applied to this listing.']);
            exit;
        }

        $pdo->beginTransaction();

        // 2. Insert application
        $stmt = $pdo->prepare("INSERT INTO applications (listing_id, applicant_id, message) VALUES (?, ?, ?)");
        $stmt->execute([$listing_id, $applicant_id, $message]);
        
        // 3. Create Notification for the listing owner
        // First get the owner
        $ownerStmt = $pdo->prepare("SELECT user_id, title FROM listings WHERE listing_id = ?");
        $ownerStmt->execute([$listing_id]);
        $listing = $ownerStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($listing && $listing['user_id'] != $applicant_id) {
            $notifMsg = $_SESSION['user']['name'] . " applied to your listing: " . $listing['title'];
            $notifStmt = $pdo->prepare("INSERT INTO notifications (user_id, type, message, link) VALUES (?, 'application', ?, ?)");
            $notifStmt->execute([$listing['user_id'], $notifMsg, 'dashboard.html']);
        }
        
        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Application submitted successfully.']);
    } catch (PDOException $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
}
?>
