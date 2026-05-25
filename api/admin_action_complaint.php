<?php
require 'db.php';
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id = $input['id'] ?? null;
$action = $input['action'] ?? null; // 'Under Review' or 'Resolved'

if (!$id || !$action) {
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

$statusMap = [
    'Under Review' => 'under_review',
    'Resolved' => 'resolved'
];

if (!isset($statusMap[$action])) {
    echo json_encode(['success' => false, 'error' => 'Invalid action']);
    exit;
}

try {
    $dbStatus = $statusMap[$action];
    $sql = "UPDATE complaints SET status = :status";
    if ($dbStatus === 'resolved') {
        $sql .= ", resolved_at = CURRENT_TIMESTAMP";
    }
    $sql .= " WHERE complaint_id = :id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['status' => $dbStatus, 'id' => $id]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
