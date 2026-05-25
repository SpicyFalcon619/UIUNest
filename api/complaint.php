<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    
    $complainant_id = $_SESSION['user_id'];
    $listing_id = $input['listingId'] ?? null;
    $against_user_id = $input['againstUserId'] ?? null;
    $category = $input['category'] ?? 'other';
    $description = $input['desc'] ?? '';
    
    if (!$against_user_id || !$description) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    // Validate category enum
    $valid_categories = ['hidden_costs', 'harassment', 'deposit_not_returned', 'misrepresentation', 'other'];
    if (!in_array($category, $valid_categories)) {
        $category = 'other';
    }
    
    try {
        $stmt = $pdo->prepare("INSERT INTO complaints (complainant_id, against_user_id, listing_id, category, description, status) VALUES (?, ?, ?, ?, ?, 'submitted')");
        $stmt->execute([$complainant_id, $against_user_id, $listing_id, $category, $description]);
        
        echo json_encode(['success' => true, 'message' => 'Complaint filed. Admin will review.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
}
?>
