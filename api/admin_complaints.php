<?php
require 'db.php';
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT 
            c.complaint_id as id,
            c.complainant_id as userId,
            u.email as userEmail,
            c.against_user_id as ownerId,
            c.listing_id as listingId,
            l.title as listingTitle,
            c.category,
            c.description,
            c.status,
            DATE_FORMAT(c.created_at, '%Y-%m-%d') as date
        FROM complaints c
        LEFT JOIN users u ON c.complainant_id = u.user_id
        LEFT JOIN listings l ON c.listing_id = l.listing_id
        ORDER BY c.created_at DESC
    ");
    $complaints = $stmt->fetchAll();

    // Fix enum cases to match frontend expectations
    foreach ($complaints as &$c) {
        $c['id'] = (int)$c['id'];
        $c['userId'] = (int)$c['userId'];
        $c['ownerId'] = (int)$c['ownerId'];
        $c['listingId'] = (int)$c['listingId'];
        
        if ($c['status'] === 'submitted') $c['status'] = 'Submitted';
        else if ($c['status'] === 'under_review') $c['status'] = 'Under Review';
        else if ($c['status'] === 'resolved') $c['status'] = 'Resolved';
        
        $c['category'] = ucwords(str_replace('_', ' ', $c['category']));
    }

    echo json_encode(['success' => true, 'complaints' => $complaints]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
