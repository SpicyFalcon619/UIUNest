<?php
require 'db.php';
$u1 = $pdo->query("SELECT user_id FROM users LIMIT 1")->fetchColumn();
$u2 = $pdo->query("SELECT user_id FROM users WHERE user_id != $u1 LIMIT 1")->fetchColumn();
$l1 = $pdo->query("SELECT listing_id FROM listings LIMIT 1")->fetchColumn();

if ($u1 && $u2 && $l1) {
    $stmt = $pdo->prepare("INSERT INTO complaints (complainant_id, against_user_id, listing_id, category, description, status) VALUES (?, ?, ?, 'other', 'Testing complaint', 'submitted')");
    $stmt->execute([$u1, $u2, $l1]);
    echo "Inserted dummy complaint successfully.";
} else {
    echo "Not enough data to insert complaint.";
}
?>
