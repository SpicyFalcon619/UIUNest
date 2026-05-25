<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'api/db.php';
$stmt = $pdo->query("SELECT * FROM seeking_posts ORDER BY created_at DESC LIMIT 5");
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($posts, JSON_PRETTY_PRINT);
