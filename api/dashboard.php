<?php
require 'db.php';
session_start();
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

$userId = $_SESSION['user']['id'];
$userRole = $_SESSION['user']['role'];

try {
    $data = [
        'myListings' => [],
        'myItems' => [],
        'watched' => [],
        'offersSent' => [],
        'offersRecv' => [],
        'appsSent' => [],
        'appsRecv' => [],
        'mySeeking' => [],
        'hasPreferences' => false
    ];

    // 1. Check Preferences
    $stmt = $pdo->prepare("SELECT pref_id FROM user_preferences WHERE user_id = ?");
    $stmt->execute([$userId]);
    $data['hasPreferences'] = $stmt->rowCount() > 0;

    // 1b. Check Verification Status
    $stmt = $pdo->prepare("SELECT status FROM verifications WHERE user_id = ? ORDER BY submitted_at DESC LIMIT 1");
    $stmt->execute([$userId]);
    $verif = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['verifStatus'] = $verif ? $verif['status'] : 'none';

    // 2. My Listings
    $stmt = $pdo->prepare("
        SELECT l.*, z.zone_name, c.total_monthly 
        FROM listings l 
        JOIN zones z ON l.zone_id = z.zone_id 
        LEFT JOIN utility_costs c ON l.listing_id = c.listing_id
        WHERE l.user_id = ?
        ORDER BY l.created_at DESC
    ");
    $stmt->execute([$userId]);
    $data['myListings'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. My Exchange Items
    $stmt = $pdo->prepare("SELECT * FROM items WHERE seller_id = ? ORDER BY created_at DESC");
    $stmt->execute([$userId]);
    $data['myItems'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. Watchlisted
    $stmt = $pdo->prepare("
        SELECT l.*, z.zone_name, c.total_monthly, c.base_rent
        FROM watchlists w
        JOIN listings l ON w.listing_id = l.listing_id
        JOIN zones z ON l.zone_id = z.zone_id
        LEFT JOIN utility_costs c ON l.listing_id = c.listing_id
        WHERE w.user_id = ?
        ORDER BY w.added_at DESC
    ");
    $stmt->execute([$userId]);
    $data['watched'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Offers Sent
    $stmt = $pdo->prepare("
        SELECT o.*, i.title, u.name as seller_name 
        FROM offers o 
        JOIN items i ON o.item_id = i.item_id 
        JOIN users u ON i.seller_id = u.user_id 
        WHERE o.buyer_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$userId]);
    $data['offersSent'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 6. Offers Received
    $stmt = $pdo->prepare("
        SELECT o.*, i.title, u.name as buyer_name 
        FROM offers o 
        JOIN items i ON o.item_id = i.item_id 
        JOIN users u ON o.buyer_id = u.user_id 
        WHERE i.seller_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$userId]);
    $data['offersRecv'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7. Applications Sent
    $stmt = $pdo->prepare("
        SELECT a.*, l.title as listing_title, u.email as owner_email 
        FROM applications a 
        JOIN listings l ON a.listing_id = l.listing_id 
        JOIN users u ON l.user_id = u.user_id 
        WHERE a.applicant_id = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$userId]);
    $data['appsSent'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 8. Applications Received
    $stmt = $pdo->prepare("
        SELECT a.*, l.title as listing_title, u.name as applicant_name 
        FROM applications a 
        JOIN listings l ON a.listing_id = l.listing_id 
        JOIN users u ON a.applicant_id = u.user_id 
        WHERE l.user_id = ?
        ORDER BY a.created_at DESC
    ");
    $stmt->execute([$userId]);
    $data['appsRecv'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 8. My Seeking Posts
    $stmt = $pdo->prepare("
        SELECT s.*, z.zone_name as zone 
        FROM seeking_posts s 
        JOIN zones z ON s.zone_id = z.zone_id 
        WHERE s.user_id = ?
        ORDER BY s.created_at DESC
    ");
    $stmt->execute([$userId]);
    $data['mySeeking'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
