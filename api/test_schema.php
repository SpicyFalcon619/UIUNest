<?php
require 'db.php';
$stmt = $pdo->query("DESCRIBE monthly_bills");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($res, JSON_PRETTY_PRINT);
?>
