<?php
require 'db.php';
$stmt = $pdo->query("SELECT * FROM complaints");
$res = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($res, JSON_PRETTY_PRINT);
?>
