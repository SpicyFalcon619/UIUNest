<?php
require 'db.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS seeking_responses (
        response_id INT AUTO_INCREMENT PRIMARY KEY,
        post_id INT,
        responder_id INT,
        message TEXT NULL,
        status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (post_id) REFERENCES seeking_posts(post_id) ON DELETE CASCADE,
        FOREIGN KEY (responder_id) REFERENCES users(user_id) ON DELETE CASCADE
    );

    CREATE TABLE IF NOT EXISTS notifications (
        notif_id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        type VARCHAR(50),
        message TEXT,
        is_read BOOLEAN DEFAULT FALSE,
        link VARCHAR(255) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
    );
    ";
    $pdo->exec($sql);
    echo "Successfully created seeking_responses and notifications tables.\n";
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
