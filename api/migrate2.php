<?php
require 'db.php';
try {
    $pdo->exec("ALTER TABLE seeking_posts CHANGE room_type property_type ENUM('single_room', 'shared_room', 'full_mess', 'sublet', 'any') NOT NULL");
    
    // Convert existing 'single' and 'shared' data
    $pdo->exec("UPDATE seeking_posts SET property_type = 'single_room' WHERE property_type = 'single'");
    $pdo->exec("UPDATE seeking_posts SET property_type = 'shared_room' WHERE property_type = 'shared'");
    
    echo "Success";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
