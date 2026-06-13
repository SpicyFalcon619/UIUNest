<?php
require 'api/db.php';
$stmt = $pdo->query('SELECT * FROM listings ORDER BY listing_id DESC LIMIT 5');
$listings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cStmt = $pdo->query('SELECT * FROM utility_costs ORDER BY cost_id DESC LIMIT 5');
$costs = $cStmt->fetchAll(PDO::FETCH_ASSOC);

echo "--- LISTINGS ---\n";
print_r($listings);
echo "\n--- COSTS ---\n";
print_r($costs);

// Also check how many listings are totally available
$cnt = $pdo->query('SELECT COUNT(*) FROM listings')->fetchColumn();
echo "\nTotal listings: $cnt\n";
