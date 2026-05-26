<?php
require 'db.php';

try {
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
    
    // Check if tables exist. If not, load schema.sql and create them
    try {
        $pdo->query("SELECT 1 FROM users LIMIT 1");
        
        // Tables exist, just truncate them
        $tables = [
            'users', 'listings', 'complaints', 'monthly_bills', 'watchlists',
            'utility_costs', 'listing_amenities', 'user_preferences', 'seeking_posts',
            'bill_payments', 'rent_history', 'items', 'offers', 'applications',
            'verifications', 'reviews', 'zones'
        ];
        
        foreach ($tables as $table) {
            $pdo->exec("TRUNCATE TABLE $table");
        }
    } catch (Exception $e) {
        // Tables don't exist, create them from schema.sql
        $sql = file_get_contents(__DIR__ . '/../database/schema.sql');
        // Remove the database creation lines since Railway manages the DB name
        $sql = str_replace("CREATE DATABASE IF NOT EXISTS uiunest;", "", $sql);
        $sql = str_replace("USE uiunest;", "", $sql);
        
        // Split and execute statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($statements as $stmt) {
            if (!empty($stmt)) {
                $pdo->exec($stmt);
            }
        }
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
