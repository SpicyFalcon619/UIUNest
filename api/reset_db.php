<?php
require 'db.php';

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    // Truncate tables
    $tables = [
        'users', 'listings', 'complaints', 'monthly_bills', 'watchlists',
        'utility_costs', 'listing_amenities', 'user_preferences', 'seeking_posts',
        'bill_payments', 'rent_history', 'items', 'offers', 'applications',
        'verifications', 'reviews'
    ];
    
    foreach ($tables as $table) {
        $pdo->exec("TRUNCATE TABLE $table");
    }
    
    // Make university_id unique if not already
    try {
        $pdo->exec("ALTER TABLE users ADD UNIQUE (university_id)");
        $pdo->exec("ALTER TABLE users ADD COLUMN profile_pic VARCHAR(255) NULL");
    } catch(Exception $e) {
        // Might already exist, ignore error safely
    }

    // Insert master admin
    $hash = password_hash('1265Master', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password_hash, role, gender, university_id, status, profile_pic) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Master Admin', 'master@admin.com', $hash, 'admin', 'other', 'ADM-MASTER', 'active', null]);
    
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    
    echo "Database successfully reset and master admin created.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
