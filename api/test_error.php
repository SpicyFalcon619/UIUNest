<?php
require 'db.php';
try {
    $pdo->exec("ALTER TABLE users ADD COLUMN profile_pic VARCHAR(255) NULL");
    echo "Column added.";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
