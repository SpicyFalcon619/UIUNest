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
    $complainant_id = $_SESSION['user_id'];
    $listing_id = $_POST['listingId'] ?? null;
    $against_user_id = $_POST['againstUserId'] ?? null;
    $category = $_POST['category'] ?? 'other';
    $description = $_POST['desc'] ?? null;
    
    if (!$against_user_id || !$description) {
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }
    
    // Validate category enum
    $valid_categories = ['hidden_costs', 'harassment', 'deposit_not_returned', 'misrepresentation', 'other'];
    if (!in_array($category, $valid_categories)) {
        $category = 'other';
    }

    $document_path = null;
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/complaints/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $fileName = time() . '_' . basename($_FILES['document']['name']);
        $targetFile = $uploadDir . $fileName;
        
        $ext = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'pdf', 'webp'])) {
            if (move_uploaded_file($_FILES['document']['tmp_name'], $targetFile)) {
                $document_path = 'uploads/complaints/' . $fileName;
            }
        }
    }
    
    try {
        $stmt = $pdo->prepare("
            INSERT INTO complaints (complainant_id, against_user_id, listing_id, category, description, status, document_path) 
            VALUES (?, ?, ?, ?, ?, 'submitted', ?)
        ");
        $stmt->execute([
            $complainant_id,
            $against_user_id,
            $listing_id,
            $category,
            $description,
            $document_path
        ]);
        
        echo json_encode(['success' => true, 'message' => 'Complaint filed. Admin will review.']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
}
?>
