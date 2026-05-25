<?php
session_start();
header('Content-Type: application/json');
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user']['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->query("SELECT u.user_id as id, u.name, u.email, u.role, u.status, 
                        (SELECT status FROM verifications v WHERE v.user_id = u.user_id ORDER BY submitted_at DESC LIMIT 1) as verifStatus 
                        FROM users u ORDER BY u.user_id DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'users' => $users]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
