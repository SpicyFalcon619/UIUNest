<?php
require 'api/db.php';

$users = [
    ['name' => 'Super Admin', 'email' => 'admin@uiunest.bd', 'password' => 'admin123', 'role' => 'admin'],
    ['name' => 'Mr. Landlord', 'email' => 'landlord@uiunest.bd', 'password' => 'password123', 'role' => 'landlord'],
    ['name' => 'Alex Student', 'email' => 'student@uiunest.bd', 'password' => 'password123', 'role' => 'student']
];

foreach ($users as $u) {
    $hash = password_hash($u['password'], PASSWORD_BCRYPT);
    try {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->execute([$u['name'], $u['email'], $hash, $u['role']]);
        echo "Created " . $u['email'] . "\n";
    } catch(Exception $e) {
        echo "Skipped " . $u['email'] . " (Already exists?)\n";
    }
}
?>
