<?php
require 'db.php';
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    // 1. Total Listings
    $stmt = $pdo->query("SELECT COUNT(*) FROM listings");
    $totalListings = $stmt->fetchColumn();

    // 2. Open Complaints
    $stmt = $pdo->query("SELECT COUNT(*) FROM complaints WHERE status != 'resolved'");
    $openComplaints = $stmt->fetchColumn();

    // 3. Avg Rent by Zone
    $stmt = $pdo->query("
        SELECT z.zone_name as zone, COALESCE(AVG(uc.total_monthly), 0) as avg
        FROM zones z
        LEFT JOIN listings l ON z.zone_id = l.zone_id
        LEFT JOIN utility_costs uc ON l.listing_id = uc.listing_id
        GROUP BY z.zone_id, z.zone_name
    ");
    $avgRentByZone = $stmt->fetchAll();
    
    foreach ($avgRentByZone as &$row) {
        $row['avg'] = round((float)$row['avg']);
    }

    // 4. Seeking vs Listings by Zone
    $stmt = $pdo->query("
        SELECT 
            z.zone_name as zone,
            (SELECT COUNT(*) FROM seeking_posts sp WHERE sp.zone_id = z.zone_id AND sp.status = 'active') as seeking,
            (SELECT COUNT(*) FROM listings l WHERE l.zone_id = z.zone_id) as listings
        FROM zones z
    ");
    $seekingVsListings = $stmt->fetchAll();

    foreach ($seekingVsListings as &$row) {
        $row['seeking'] = (int)$row['seeking'];
        $row['listings'] = (int)$row['listings'];
    }

    echo json_encode([
        'success' => true,
        'stats' => [
            'totalListings' => (int)$totalListings,
            'openComplaints' => (int)$openComplaints,
            'avgRentByZone' => $avgRentByZone,
            'seekingVsListings' => $seekingVsListings
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
