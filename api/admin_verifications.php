<?php
require 'db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    // Fetch pending verifications with user details
    $stmt = $pdo->query("
        SELECT v.*, u.name, u.email 
        FROM verifications v
        JOIN users u ON v.user_id = u.user_id
        ORDER BY v.submitted_at DESC
    ");
    $verifs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'verifications' => $verifs]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
